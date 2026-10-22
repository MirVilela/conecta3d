<?php
/**
 * sair.php
 * Não tem tela própria: só encerra a sessão e redireciona
 * de volta para o login.
 */
require 'conexao.php';

$_SESSION = [];
session_destroy();

header('Location: index.php');
exit;
