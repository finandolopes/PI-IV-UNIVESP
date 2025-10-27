<?php
session_start();
include_once('../php/conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit();
}

header('Content-Type: application/json');

$id_depoimento = isset($_POST['id_depoimento']) ? (int)$_POST['id_depoimento'] : 0;
$acao = isset($_POST['acao']) ? $_POST['acao'] : '';
$motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';

if (!$id_depoimento || !in_array($acao, ['aprovar', 'reprovar'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit();
}

// Verificar se o depoimento existe e está pendente
$query = 'SELECT status FROM depoimentos WHERE id_depoimento = ?';
$stmt = $conexao->prepare($query);
$stmt->bind_param('i', $id_depoimento);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Depoimento não encontrado.']);
    exit();
}

$depoimento = $result->fetch_assoc();
$stmt->close();

if ($depoimento['status'] !== 'pendente') {
    echo json_encode(['success' => false, 'message' => 'Este depoimento já foi moderado.']);
    exit();
}

// Definir novo status e motivo
$novo_status = '';
$motivo_reprovacao = null;

if ($acao === 'aprovar') {
    $novo_status = 'aprovado';
} elseif ($acao === 'reprovar') {
    $novo_status = 'reprovado';
    if (empty($motivo)) {
        echo json_encode(['success' => false, 'message' => 'Motivo da reprovação é obrigatório.']);
        exit();
    }
    $motivo_reprovacao = $motivo;
}

// Atualizar depoimento
$query = 'UPDATE depoimentos SET status = ?, motivo_reprovacao = ?, data_moderacao = NOW(), moderador_id = ? WHERE id_depoimento = ?';
$stmt = $conexao->prepare($query);
$stmt->bind_param('ssii', $novo_status, $motivo_reprovacao, $_SESSION['id_usuario'], $id_depoimento);

if ($stmt->execute()) {
    // Log da ação
    $acao_log = ucfirst($acao) . ' depoimento #' . $id_depoimento;
    $query_log = 'INSERT INTO logs (id_usuario, acao, data_hora, ip) VALUES (?, ?, NOW(), ?)';
    $stmt_log = $conexao->prepare($query_log);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt_log->bind_param('iss', $_SESSION['id_usuario'], $acao_log, $ip);
    $stmt_log->execute();
    $stmt_log->close();

    echo json_encode([
        'success' => true,
        'message' => 'Depoimento ' . ($acao === 'aprovar' ? 'aprovado' : 'reprovado') . ' com sucesso!',
        'novo_status' => $novo_status
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao processar depoimento: ' . $stmt->error]);
}

$stmt->close();
$conexao->close();
?>