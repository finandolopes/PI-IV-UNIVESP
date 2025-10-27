<?php
// Ativar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log de debug
error_log("Iniciando admin_test.php");

// Incluir conexão
include_once('../php/conexao.php');
error_log("Conexão incluída");

// Verificar sessão
session_start();
error_log("Sessão iniciada");

if (!isset($_SESSION['username'])) {
    error_log("Usuário não logado, redirecionando para login.php");
    header('Location: login.php');
    exit();
}

error_log("Usuário logado: " . $_SESSION['username']);

// Teste simples de consulta
$query_test = "SELECT 1 as test";
$result_test = mysqli_query($conexao, $query_test);
if (!$result_test) {
    error_log("Erro na consulta de teste: " . mysqli_error($conexao));
    die("Erro na consulta de teste: " . mysqli_error($conexao));
}

$row_test = mysqli_fetch_assoc($result_test);
error_log("Consulta de teste executada com sucesso: " . $row_test['test']);

echo "<h1>Admin Test - Funcionando!</h1>";
echo "<p>Usuário: " . htmlspecialchars($_SESSION['username']) . "</p>";
echo "<p>Data/Hora: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Consulta de teste: " . $row_test['test'] . "</p>";
?>