<?php
require 'conexao.php';
exigirLogin();

$tipo = $_SESSION['usuario_tipo'];
$uid  = $_SESSION['usuario_id'];
$id   = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT d.*, s.nome AS solicitante_nome, v.nome AS voluntario_nome
     FROM demandas d
     JOIN usuarios s ON s.id = d.solicitante_id
     LEFT JOIN usuarios v ON v.id = d.voluntario_id
     WHERE d.id = :id'
);
$stmt->execute(['id' => $id]);
$demanda = $stmt->fetch();

if (!$demanda) {
    http_response_code(404);
    require 'topo.php';
    echo '<div class="alerta alerta-erro">Demanda não encontrada.</div>';
    require 'rodape.php';
    exit;
}

// Regras de visualização: solicitante só vê a própria; voluntário vê
// abertas ou as que assumiu; admin vê tudo.
$podeVer =
    $tipo === 'admin' ||
    ($tipo !== 'voluntario' && $demanda['solicitante_id'] == $uid) ||
    ($tipo === 'voluntario' && ($demanda['status'] === 'aberta' || $demanda['voluntario_id'] == $uid));

if (!$podeVer) {
    http_response_code(403);
    require 'topo.php';
    echo '<div class="alerta alerta-erro">Você não tem permissão para ver esta demanda.</div>';
    require 'rodape.php';
    exit;
}

$mensagem = '';
$erro     = '';

// --- Ação exclusiva de admin: excluir a demanda por completo ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {
    if ($tipo === 'admin') {
        // ON DELETE CASCADE na tabela historico_status já remove o histórico junto
        $stmt = $pdo->prepare('DELETE FROM demandas WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
    header('Location: painel.php');
    exit;
}

// --- Processa mudança de status ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $statusAtual = $demanda['status'];
    $novoStatus  = null;
    $voluntarioIdNovo = $demanda['voluntario_id'];
    $observacao = '';
    $statusValidos = ['aberta', 'em_andamento', 'concluida', 'cancelada'];

    if ($acao === 'aceitar' && $tipo === 'voluntario' && $statusAtual === 'aberta') {
        $novoStatus = 'em_andamento';
        $voluntarioIdNovo = $uid;
        $observacao = 'Voluntário aceitou a demanda.';

    } elseif ($acao === 'concluir' && $tipo === 'voluntario'
              && $statusAtual === 'em_andamento' && $demanda['voluntario_id'] == $uid) {
        $novoStatus = 'concluida';
        $observacao = 'Peça concluída pelo voluntário.';

    } elseif ($acao === 'cancelar'
              && in_array($statusAtual, ['aberta', 'em_andamento'])
              && ($tipo === 'admin' || ($tipo !== 'voluntario' && $demanda['solicitante_id'] == $uid))) {
        $novoStatus = 'cancelada';
        $observacao = 'Demanda cancelada pelo solicitante.';

    } elseif ($acao === 'admin_status' && $tipo === 'admin') {
        // Admin pode forçar qualquer status, de qualquer status atual
        $statusForcado = $_POST['novo_status'] ?? '';
        if (in_array($statusForcado, $statusValidos, true)) {
            $novoStatus = $statusForcado;
            $observacao = 'Status alterado manualmente pelo administrador.';
        } else {
            $erro = 'Status inválido.';
        }

    } else {
        $erro = 'Ação inválida para o status atual.';
    }

    if ($novoStatus !== null) {
        $pdo->beginTransaction();

        $stmtUpd = $pdo->prepare(
            'UPDATE demandas SET status = :status, voluntario_id = :voluntario_id WHERE id = :id'
        );
        $stmtUpd->execute([
            'status'       => $novoStatus,
            'voluntario_id'=> $voluntarioIdNovo,
            'id'           => $id,
        ]);

        $stmtHist = $pdo->prepare(
            'INSERT INTO historico_status (demanda_id, usuario_id, status_anterior, status_novo, observacao)
             VALUES (:demanda_id, :usuario_id, :status_anterior, :status_novo, :observacao)'
        );
        $stmtHist->execute([
            'demanda_id'      => $id,
            'usuario_id'      => $uid,
            'status_anterior' => $statusAtual,
            'status_novo'     => $novoStatus,
            'observacao'      => $observacao,
        ]);

        $pdo->commit();

        header('Location: demanda.php?id=' . $id);
        exit;
    }
}

// Recarrega a demanda (caso tenha mudado) e o histórico
$stmt->execute(['id' => $id]);
$demanda = $stmt->fetch();

$stmtHist = $pdo->prepare(
    'SELECT h.*, u.nome AS usuario_nome
     FROM historico_status h
     JOIN usuarios u ON u.id = h.usuario_id
     WHERE h.demanda_id = :id
     ORDER BY h.data_alteracao ASC'
);
$stmtHist->execute(['id' => $id]);
$historico = $stmtHist->fetchAll();

require 'topo.php';
?>

<a href="painel.php">&larr; Voltar ao painel</a>

<div class="card" style="margin-top:12px;">
    <div style="display:flex; justify-content:space-between; align-items:start; gap:12px;">
        <h2 style="margin-top:0;"><?= htmlspecialchars($demanda['titulo']) ?></h2>
        <span class="status status-<?= htmlspecialchars($demanda['status']) ?>">
            <?= htmlspecialchars(str_replace('_', ' ', $demanda['status'])) ?>
        </span>
    </div>

    <p><strong>Tipo de peça:</strong> <?= htmlspecialchars($demanda['tipo_peca'] ?: 'Não informado') ?></p>
    <p><strong>Descrição:</strong><br><?= nl2br(htmlspecialchars($demanda['descricao'])) ?></p>
    <p><strong>Solicitante:</strong> <?= htmlspecialchars($demanda['solicitante_nome']) ?></p>
    <p><strong>Voluntário:</strong>
        <?= $demanda['voluntario_nome'] ? htmlspecialchars($demanda['voluntario_nome']) : 'Ainda não assumida' ?>
    </p>

    <?php if ($erro !== ''): ?>
        <div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <div style="display:flex; gap:8px; margin-top:12px;">
        <?php if ($tipo === 'voluntario' && $demanda['status'] === 'aberta'): ?>
            <form method="post">
                <input type="hidden" name="acao" value="aceitar">
                <button type="submit" class="botao">Aceitar demanda</button>
            </form>
        <?php endif; ?>

        <?php if ($tipo === 'voluntario' && $demanda['status'] === 'em_andamento' && $demanda['voluntario_id'] == $uid): ?>
            <form method="post">
                <input type="hidden" name="acao" value="concluir">
                <button type="submit" class="botao">Marcar como concluída</button>
            </form>
        <?php endif; ?>

        <?php if (in_array($demanda['status'], ['aberta', 'em_andamento'])
                  && ($tipo === 'admin' || ($tipo !== 'voluntario' && $demanda['solicitante_id'] == $uid))): ?>
            <form method="post" onsubmit="return confirm('Tem certeza que deseja cancelar esta demanda?');">
                <input type="hidden" name="acao" value="cancelar">
                <button type="submit" class="botao botao-secundario">Cancelar demanda</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($tipo === 'admin'): ?>
        <hr style="margin:20px 0; border:none; border-top:1px solid var(--cor-borda);">
        <h4 style="margin-bottom:8px;">Área do administrador</h4>

        <form method="post" style="display:flex; gap:8px; align-items:center; margin-bottom:12px;">
            <input type="hidden" name="acao" value="admin_status">
            <select name="novo_status">
                <option value="aberta"       <?= $demanda['status'] === 'aberta' ? 'selected' : '' ?>>aberta</option>
                <option value="em_andamento" <?= $demanda['status'] === 'em_andamento' ? 'selected' : '' ?>>em andamento</option>
                <option value="concluida"    <?= $demanda['status'] === 'concluida' ? 'selected' : '' ?>>concluída</option>
                <option value="cancelada"    <?= $demanda['status'] === 'cancelada' ? 'selected' : '' ?>>cancelada</option>
            </select>
            <button type="submit" class="botao botao-secundario">Forçar status</button>
        </form>

        <form method="post" onsubmit="return confirm('Excluir esta demanda de vez? Essa ação não pode ser desfeita.');">
            <input type="hidden" name="acao" value="excluir">
            <button type="submit" class="botao" style="background:var(--cor-status-cancelada);">
                Excluir demanda permanentemente
            </button>
        </form>
    <?php endif; ?>
</div>

<h3>Histórico</h3>
<?php if (empty($historico)): ?>
    <p>Sem registros de histórico ainda.</p>
<?php else: ?>
    <?php foreach ($historico as $h): ?>
        <div class="card">
            <p style="margin:0;">
                <strong><?= htmlspecialchars($h['usuario_nome']) ?></strong>
                —
                <?= $h['status_anterior'] ? htmlspecialchars(str_replace('_', ' ', $h['status_anterior'])) . ' → ' : '' ?>
                <?= htmlspecialchars(str_replace('_', ' ', $h['status_novo'])) ?>
            </p>
            <?php if ($h['observacao']): ?>
                <p style="margin:4px 0 0; color:var(--cor-texto-suave); font-size:0.9rem;">
                    <?= htmlspecialchars($h['observacao']) ?>
                </p>
            <?php endif; ?>
            <p style="margin:4px 0 0; color:var(--cor-texto-suave); font-size:0.8rem;">
                <?= htmlspecialchars($h['data_alteracao']) ?>
            </p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require 'rodape.php'; ?>
