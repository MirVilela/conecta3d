<?php
/**
 * topo.php
 * Cabeçalho e menu comuns. Deve ser incluído logo depois de
 * conexao.php nas páginas que já têm sessão iniciada.
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conecta3D</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<header class="topo">
    <div class="topo-marca">
        <span class="topo-logo">Conecta3D</span>
    </div>

    <?php if (isset($_SESSION['usuario_id'])): ?>
        <nav class="topo-menu">
            <a href="painel.php">Painel</a>
            <?php if (in_array($_SESSION['usuario_tipo'], ['solicitante', 'clinica'], true)): ?>
                <a href="nova_demanda.php">Nova demanda</a>
            <?php endif; ?>
        </nav>

        <div class="topo-usuario">
            <span class="topo-usuario-nome">
                <?= htmlspecialchars($_SESSION['usuario_nome']) ?>
                <small>(<?= htmlspecialchars($_SESSION['usuario_tipo']) ?>)</small>
            </span>
            <a href="sair.php" class="botao botao-sair">Sair</a>
        </div>
    <?php endif; ?>
</header>

<main class="conteudo">
