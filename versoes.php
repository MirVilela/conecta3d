<?php
/**
 * versoes.php
 * ATENÇÃO: arquivo temporário só para checar as versões do ambiente.
 * Pode apagar depois de anotar o resultado.
 */
echo '<h2>Versões do ambiente</h2><ul>';

echo '<li><strong>PHP:</strong> ' . htmlspecialchars(PHP_VERSION) . '</li>';

if (function_exists('apache_get_version')) {
    echo '<li><strong>Apache:</strong> ' . htmlspecialchars(apache_get_version()) . '</li>';
} else {
    echo '<li><strong>Apache:</strong> não foi possível detectar automaticamente (PHP rodando via módulo diferente de mod_php)</li>';
}

echo '<li><strong>Extensão PDO MySQL:</strong> ' . (extension_loaded('pdo_mysql') ? 'carregada' : 'NÃO carregada') . '</li>';

require 'conexao.php';
$versaoBanco = $pdo->query('SELECT VERSION()')->fetchColumn();
echo '<li><strong>MySQL/MariaDB:</strong> ' . htmlspecialchars($versaoBanco) . '</li>';

echo '</ul>';