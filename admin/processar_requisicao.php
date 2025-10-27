<?php
session_start();
include_once('../php/conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
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

$id_requisicao = isset($_POST['id_requisicao']) ? (int)$_POST['id_requisicao'] : 0;
$acao = isset($_POST['acao']) ? $_POST['acao'] : '';
$observacoes = isset($_POST['observacoes']) ? trim($_POST['observacoes']) : '';

if (!$id_requisicao || !in_array($acao, ['aprovar', 'reprovar', 'cancelar'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit();
}

// Verificar se a requisição existe e está em análise
$query = 'SELECT status, id_cliente FROM requisicoes WHERE id_requisicao = ?';
$stmt = $conexao->prepare($query);
$stmt->bind_param('i', $id_requisicao);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Requisição não encontrada.']);
    exit();
}

$row = $result->fetch_assoc();
$stmt->close();

if ($row['status'] !== 'em_analise') {
    echo json_encode(['success' => false, 'message' => 'Esta requisição não está em análise.']);
    exit();
}

// Definir novo status
$novo_status = '';
switch ($acao) {
    case 'aprovar':
        $novo_status = 'aprovado';
        break;
    case 'reprovar':
        $novo_status = 'reprovado';
        break;
    case 'cancelar':
        $novo_status = 'cancelado';
        break;
}

// Atualizar requisição
$query = 'UPDATE requisicoes SET status = ?, analista_id = ?, data_analise = NOW(), observacoes = ? WHERE id_requisicao = ?';
$stmt = $conexao->prepare($query);
$stmt->bind_param('sissi', $novo_status, $_SESSION['usuario_id'], $observacoes, $id_requisicao);

if ($stmt->execute()) {
    // Log da ação
    $acao_log = ucfirst($acao) . ' requisição #' . $id_requisicao;
    $tabela_afetada = 'requisicoes';
    $dados_novos = 'status: ' . $novo_status;
    $tipo_log = 'UPDATE';
    $query_log = 'INSERT INTO logs_sistema (usuario_id, acao, tabela_afetada, registro_id, dados_novos, tipo) VALUES (?, ?, ?, ?, ?, ?)';
    $stmt_log = $conexao->prepare($query_log);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt_log->bind_param('ississs', $_SESSION['usuario_id'], $acao_log, $tabela_afetada, $id_requisicao, $dados_novos, $tipo_log);
    $stmt_log->execute();
    $stmt_log->close();

    // Se aprovado, atualizar estatísticas do cliente
    if ($novo_status === 'aprovado') {
        $query_stats = 'UPDATE clientes SET creditos_aprovados = creditos_aprovados + 1 WHERE id_cliente = ?';
        $stmt_stats = $conexao->prepare($query_stats);
        $stmt_stats->bind_param('i', $row['id_cliente']);
        $stmt_stats->execute();
        $stmt_stats->close();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Requisição ' . ($acao === 'aprovar' ? 'aprovada' : ($acao === 'reprovar' ? 'reprovada' : 'cancelada')) . ' com sucesso!',
        'novo_status' => $novo_status
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao processar requisição: ' . $stmt->error]);
}

$stmt->close();
$conexao->close();
?>
