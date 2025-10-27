<?php
session_start();
include_once('../php/conexao.php');

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION['username']) || $_SESSION['tipo'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Não autorizado']);
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID inválido']);
    exit();
}

$user_id = $_GET['id'];

$stmt = $conexao->prepare('SELECT id_usuario, nome, email, username, tipo, status FROM usuarios WHERE id_usuario = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Usuário não encontrado']);
    exit();
}

$usuario = $result->fetch_assoc();
$stmt->close();
$conexao->close();

header('Content-Type: application/json');
echo json_encode($usuario);
?>
