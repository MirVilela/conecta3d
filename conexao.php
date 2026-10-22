<?php
/**
 * conexao.php
 * Responsável por:
 *  - Iniciar a sessão (usada em todas as páginas protegidas)
 *  - Abrir a conexão com o banco de dados via PDO
 *
 * Este arquivo deve ser o PRIMEIRO include de qualquer página
 * que precise de sessão e/ou banco de dados.
 */

session_start();

// --- Configurações de conexão ---
define('DB_HOST', '127.0.0.1');
define('DB_NOME', 'oficinahotrods_joaodavi_banco_projeto');
define('DB_USUARIO', 'root');
define('DB_SENHA', ''); // padrão do XAMPP: usuário root sem senha

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=3306;dbname=' . DB_NOME . ';charset=utf8mb4',
        DB_USUARIO,
        DB_SENHA,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Em produção, não expor $e->getMessage() diretamente.
    die('Erro ao conectar ao banco de dados: ' . $e->getMessage());
}

/**
 * Garante que só usuários logados acessem a página atual.
 * Chame esta função no topo de painel.php, nova_demanda.php
 * e demanda.php (não em index.php).
 */
function exigirLogin(): void
{
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: index.php');
        exit;
    }
}
