<?php
require 'conexao.php';

// Se já estiver logado, não faz sentido ver a tela de login de novo
if (isset($_SESSION['usuario_id'])) {
    header('Location: painel.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha e-mail e senha.';
    } else {
        $stmt = $pdo->prepare('SELECT id, nome, senha, tipo FROM usuarios WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Login válido: troca o ID da sessão (evita fixação de sessão) e
            // grava os dados usados pelo topo.php e por exigirLogin()
            session_regenerate_id(true);
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];

            header('Location: painel.php');
            exit;
        } else {
            $erro = 'E-mail ou senha inválidos.';
        }
    }
}

require 'topo.php';
?>

<div class="login-caixa">
    <h2>Entrar no Conecta3D</h2>

    <?php if ($erro !== ''): ?>
        <div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="post" action="index.php">
        <div class="form-grupo">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-grupo">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required>
        </div>

        <button type="submit" class="botao" style="width:100%">Entrar</button>
    </form>

    <p style="text-align:center; margin-top:12px;">
        Não tem conta? <a href="cadastro.php">Registrar-se</a>
    </p>
</div>

<?php require 'rodape.php'; ?>
