<?php
session_start();
include_once('../php/conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
    exit();
}

// Verificar se é admin
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit();
}

header('Content-Type: application/json');

$id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
$acao = isset($_POST['acao']) ? $_POST['acao'] : '';

if (!$id_usuario || !in_array($acao, ['desativar', 'reativar', 'excluir'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit();
}

// Não permitir ações no próprio usuário
if ($id_usuario == $_SESSION['id_usuario']) {
    echo json_encode(['success' => false, 'message' => 'Você não pode executar esta ação em sua própria conta.']);
    exit();
}

// Verificar se o usuário existe
$query = 'SELECT nome, perfil FROM adm WHERE id_usuario = ?';
$stmt = $conexao->prepare($query);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado.']);
    exit();
}

$usuario = $result->fetch_assoc();
$stmt->close();

// Não permitir desativar/reativar/excluir outros admins
if ($usuario['perfil'] === 'admin') {
    echo json_encode(['success' => false, 'message' => 'Não é possível executar esta ação em contas de administrador.']);
    exit();
}

// Executar ação
$mensagem = '';
$acao_log = $acao;

switch ($acao) {
    case 'excluir':
        // Verificar se o usuário tem requisições pendentes
        $query_check = 'SELECT COUNT(*) as pendentes FROM requisicoes WHERE analista_id = ? AND status = ?';
        $stmt_check = $conexao->prepare($query_check);
        $status_pendente = 'em_analise';
        $stmt_check->bind_param('is', $id_usuario, $status_pendente);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $check = $result_check->fetch_assoc();
        $stmt_check->close();

        if ($check['pendentes'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Não é possível excluir este usuário pois ele possui requisições em análise.']);
            exit();
        }

        $query = 'DELETE FROM adm WHERE id_usuario = ?';
        $stmt = $conexao->prepare($query);
        $stmt->bind_param('i', $id_usuario);
        $mensagem = 'Usuário excluído permanentemente!';
        break;
}

if ($stmt->execute()) {
    // Log da ação
    $acao_log = ucfirst($acao) . ' usuário #' . $id_usuario . ' (' . $usuario['nome'] . ')';
    $query_log = 'INSERT INTO logs (id_usuario, acao, data_hora, ip) VALUES (?, ?, NOW(), ?)';
    $stmt_log = $conexao->prepare($query_log);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt_log->bind_param('iss', $_SESSION['id_usuario'], $acao_log, $ip);
    $stmt_log->execute();
    $stmt_log->close();

    echo json_encode([
        'success' => true,
        'message' => $mensagem
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao executar ação: ' . $stmt->error]);
}

$stmt->close();
$conexao->close();
?>