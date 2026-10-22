<?php
require 'conexao.php';
exigirLogin();

$tipo = $_SESSION['usuario_tipo'];
$uid  = $_SESSION['usuario_id'];

// Só solicitante e clínica criam demandas; voluntário atende e admin administra
if (!in_array($tipo, ['solicitante', 'clinica'], true)) {
    header('Location: painel.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo    = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $tipoPeca  = trim($_POST['tipo_peca'] ?? '');

    // Limites iguais aos tamanhos das colunas em demandas, para o banco
    // não cortar o texto sem avisar
    if ($titulo === '' || $descricao === '') {
        $erro = 'Preencha ao menos o título e a descrição.';
    } elseif (mb_strlen($titulo) > 150) {
        $erro = 'O título pode ter no máximo 150 caracteres.';
    } elseif (mb_strlen($tipoPeca) > 100) {
        $erro = 'O tipo de peça pode ter no máximo 100 caracteres.';
    } else {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'INSERT INTO demandas (solicitante_id, titulo, descricao, tipo_peca, status)
             VALUES (:solicitante_id, :titulo, :descricao, :tipo_peca, "aberta")'
        );
        $stmt->execute([
            'solicitante_id' => $uid,
            'titulo'         => $titulo,
            'descricao'      => $descricao,
            'tipo_peca'      => $tipoPeca !== '' ? $tipoPeca : null,
        ]);

        $demandaId = $pdo->lastInsertId();

        // Primeiro registro do histórico: nasce como "aberta"
        $stmtHist = $pdo->prepare(
            'INSERT INTO historico_status (demanda_id, usuario_id, status_anterior, status_novo, observacao)
             VALUES (:demanda_id, :usuario_id, NULL, "aberta", "Demanda criada")'
        );
        $stmtHist->execute([
            'demanda_id' => $demandaId,
            'usuario_id' => $uid,
        ]);

        $pdo->commit();

        header('Location: demanda.php?id=' . $demandaId);
        exit;
    }
}

require 'topo.php';
?>

<h2>Nova demanda</h2>

<?php if ($erro !== ''): ?>
    <div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<div class="card">
    <form method="post" action="nova_demanda.php">
        <div class="form-grupo">
            <label for="titulo">Título</label>
            <input type="text" id="titulo" name="titulo" required maxlength="150"
                   value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>"
                   placeholder="Ex: Adaptador de escova de dente">
        </div>

        <div class="form-grupo">
            <label for="tipo_peca">Tipo de peça</label>
            <input type="text" id="tipo_peca" name="tipo_peca" maxlength="100"
                   value="<?= htmlspecialchars($_POST['tipo_peca'] ?? '') ?>"
                   placeholder="Ex: órtese, ferramenta ergonômica...">
        </div>

        <div class="form-grupo">
            <label for="descricao">Descrição</label>
            <textarea id="descricao" name="descricao" required
                      placeholder="Descreva a necessidade com o máximo de detalhes possível"><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="botao">Criar demanda</button>
        <a href="painel.php" class="botao botao-secundario">Cancelar</a>
    </form>
</div>

<?php require 'rodape.php'; ?>
