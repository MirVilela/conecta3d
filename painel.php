<?php
require 'conexao.php';
exigirLogin();

$tipo = $_SESSION['usuario_tipo'];
$uid  = $_SESSION['usuario_id'];

// A consulta muda conforme o papel do usuário logado:
// - solicitante/clinica: só o que ele mesmo pediu
// - voluntario: demandas abertas (disponíveis) + as que ele já assumiu
// - admin: tudo, inclusive as inativas
// Demandas inativas só aparecem para o admin.
if ($tipo === 'solicitante' || $tipo === 'clinica') {
    $sql = 'SELECT d.*, v.nome AS voluntario_nome
            FROM demandas d
            LEFT JOIN usuarios v ON v.id = d.voluntario_id
            WHERE d.solicitante_id = :uid AND d.ativa = 1
            ORDER BY d.criado_em DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $uid]);

} elseif ($tipo === 'voluntario') {
    $sql = 'SELECT d.*, s.nome AS solicitante_nome
            FROM demandas d
            JOIN usuarios s ON s.id = d.solicitante_id
            WHERE d.ativa = 1 AND (d.status = "aberta" OR d.voluntario_id = :uid)
            ORDER BY d.criado_em DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $uid]);

} else { // admin
    $sql = 'SELECT d.*, s.nome AS solicitante_nome, v.nome AS voluntario_nome
            FROM demandas d
            JOIN usuarios s ON s.id = d.solicitante_id
            LEFT JOIN usuarios v ON v.id = d.voluntario_id
            ORDER BY d.criado_em DESC';
    $stmt = $pdo->query($sql);
}

$demandas = $stmt->fetchAll();

require 'topo.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <h2 style="margin:0;">
        <?php if ($tipo === 'voluntario'): ?>
            Demandas disponíveis e suas peças
        <?php elseif ($tipo === 'admin'): ?>
            Todas as demandas
        <?php else: ?>
            Minhas demandas
        <?php endif; ?>
    </h2>
    <?php if ($tipo === 'solicitante' || $tipo === 'clinica'): ?>
        <a href="nova_demanda.php" class="botao">+ Nova demanda</a>
    <?php endif; ?>
</div>

<?php if (empty($demandas)): ?>
    <div class="card">
        <p>Nenhuma demanda encontrada por aqui ainda.</p>
    </div>
<?php else: ?>
    <?php foreach ($demandas as $d): ?>
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:start; gap:12px;">
                <div>
                    <h3 style="margin-bottom:4px;">
                        <a href="demanda.php?id=<?= (int)$d['id'] ?>">
                            <?= htmlspecialchars($d['titulo']) ?>
                        </a>
                    </h3>
                    <p style="margin:4px 0; color:var(--cor-texto-suave); font-size:0.9rem;">
                        <?= htmlspecialchars($d['tipo_peca'] ?: 'Tipo não informado') ?>

                        <?php if ($tipo === 'voluntario' && isset($d['solicitante_nome'])): ?>
                            · solicitado por <?= htmlspecialchars($d['solicitante_nome']) ?>
                        <?php elseif (($tipo === 'admin' || $tipo === 'solicitante' || $tipo === 'clinica') && !empty($d['voluntario_nome'])): ?>
                            · voluntário: <?= htmlspecialchars($d['voluntario_nome']) ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <?php if (!$d['ativa']): ?>
                        <span class="status status-inativa">inativa</span>
                    <?php endif; ?>
                    <span class="status status-<?= htmlspecialchars($d['status']) ?>">
                        <?= htmlspecialchars(str_replace('_', ' ', $d['status'])) ?>
                    </span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require 'rodape.php'; ?>
