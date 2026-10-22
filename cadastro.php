<?php
require 'conexao.php';

// Se já estiver logado, não faz sentido ver a tela de cadastro
if (isset($_SESSION['usuario_id'])) {
    header('Location: painel.php');
    exit;
}

$erro = '';
$tiposPermitidos = ['solicitante', 'clinica', 'voluntario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $senha    = $_POST['senha'] ?? '';
    $confirma = $_POST['confirma_senha'] ?? '';
    $tipo     = $_POST['tipo'] ?? '';
    $telefone = trim($_POST['telefone'] ?? '');
    $cidade   = trim($_POST['cidade'] ?? '');

    if ($nome === '' || $email === '' || $senha === '') {
        $erro = 'Preencha nome, e-mail e senha.';
    } elseif (!in_array($tipo, $tiposPermitidos, true)) {
        $erro = 'Selecione um tipo de usuário válido.';
    } elseif ($senha !== $confirma) {
        $erro = 'As senhas não conferem.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha precisa ter pelo menos 6 caracteres.';
    } else {
        // Confere se o e-mail já está em uso
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
        $stmt->execute(['email' => $email]);

        if ($stmt->fetch()) {
            $erro = 'Já existe uma conta com esse e-mail.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (nome, email, senha, tipo, telefone, cidade)
                 VALUES (:nome, :email, :senha, :tipo, :telefone, :cidade)'
            );
            $stmt->execute([
                'nome'     => $nome,
                'email'    => $email,
                'senha'    => $hash,
                'tipo'     => $tipo,
                'telefone' => $telefone !== '' ? $telefone : null,
                'cidade'   => $cidade !== '' ? $cidade : null,
            ]);

            // Já loga o usuário automaticamente após o cadastro
            $_SESSION['usuario_id']   = $pdo->lastInsertId();
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_tipo'] = $tipo;

            header('Location: painel.php');
            exit;
        }
    }
}

require 'topo.php';
?>

<div class="login-caixa" style="max-width:440px;">
    <h2>Criar conta</h2>

    <?php if ($erro !== ''): ?>
        <div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="post" action="cadastro.php">
        <div class="form-grupo">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" required
                   value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
        </div>

        <div class="form-grupo">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-grupo">
            <label for="tipo">Eu sou:</label>
            <select id="tipo" name="tipo" required>
                <option value="">Selecione...</option>
                <option value="solicitante">Solicitante (preciso de uma peça)</option>
                <option value="clinica">Clínica</option>
                <option value="voluntario">Voluntário (tenho impressora 3D)</option>
            </select>
        </div>

        <div class="form-grupo">
            <label for="telefone">Telefone (opcional)</label>
            <input type="tel" id="telefone" name="telefone"
                   value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
        </div>

        <div class="form-grupo">
            <label for="cidade">Cidade (opcional)</label>
            <input type="text" id="cidade" name="cidade"
                   value="<?= htmlspecialchars($_POST['cidade'] ?? '') ?>">
        </div>

        <div class="form-grupo">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required>
        </div>

        <div class="form-grupo">
            <label for="confirma_senha">Confirmar senha</label>
            <input type="password" id="confirma_senha" name="confirma_senha" required>
        </div>

        <button type="submit" class="botao" style="width:100%">Criar conta</button>
    </form>

    <p style="text-align:center; margin-top:12px;">
        <a href="index.php">Já tenho conta — fazer login</a>
    </p>
</div>

<?php require 'rodape.php'; ?>
