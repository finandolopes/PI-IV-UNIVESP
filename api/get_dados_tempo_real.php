<?php
// API para dados em tempo real
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include_once('../php/conexao.php');

// Função para obter visitas de hoje
function getVisitasHoje() {
    global $conexao;
    $sql = "SELECT COUNT(*) as total FROM contador_visitas WHERE DATE(data_visita) = CURDATE()";
    $result = mysqli_query($conexao, $sql);
    $row = mysqli_fetch_assoc($result);
    return (int)$row['total'];
}

// Função para obter visitas da última hora
function getVisitasUltimaHora() {
    global $conexao;
    $sql = "SELECT COUNT(*) as total FROM contador_visitas
            WHERE data_visita >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
    $result = mysqli_query($conexao, $sql);
    $row = mysqli_fetch_assoc($result);
    return (int)$row['total'];
}

// Função para obter requisições de hoje
function getRequisicoesHoje() {
    global $conexao;
    $sql = "SELECT COUNT(*) as total FROM requisicoes WHERE DATE(data_requisicao) = CURDATE()";
    $result = mysqli_query($conexao, $sql);
    $row = mysqli_fetch_assoc($result);
    return (int)$row['total'];
}

// Função para obter visitas por hora hoje
function getVisitasPorHora() {
    global $conexao;
    $sql = "SELECT HOUR(data_visita) as hora, COUNT(*) as visitas
            FROM contador_visitas
            WHERE DATE(data_visita) = CURDATE()
            GROUP BY HOUR(data_visita)
            ORDER BY hora";
    $result = mysqli_query($conexao, $sql);

    $visitas = array_fill(0, 24, 0);
    while ($row = mysqli_fetch_assoc($result)) {
        $visitas[$row['hora']] = (int)$row['visitas'];
    }

    return $visitas;
}

// Calcular taxa de conversão
$visitasHoje = getVisitasHoje();
$requisicoesHoje = getRequisicoesHoje();
$taxaConversao = $visitasHoje > 0 ? round(($requisicoesHoje / $visitasHoje) * 100, 1) : 0;

// Sistema de alertas
$alertas = [];

$visitasUltimaHora = getVisitasUltimaHora();
if ($visitasUltimaHora > 10) {
    $alertas[] = [
        'tipo' => 'pico',
        'titulo' => '🚨 Pico de Visitas!',
        'mensagem' => "Detectamos {$visitasUltimaHora} visitas na última hora. Prepare a equipe!"
    ];
}

$horaAtual = (int)date('H');
$visitasHoraAtual = getVisitasPorHora()[$horaAtual];
if ($visitasHoraAtual > 15) {
    $alertas[] = [
        'tipo' => 'pico',
        'titulo' => '⚡ Hora de Pico!',
        'mensagem' => "Esta hora teve {$visitasHoraAtual} visitas. Momento de alta atividade!"
    ];
}

// Preparar resposta JSON
$dados = [
    'visitas_hoje' => $visitasHoje,
    'visitas_ultima_hora' => $visitasUltimaHora,
    'requisicoes_hoje' => $requisicoesHoje,
    'taxa_conversao' => $taxaConversao,
    'visitas_por_hora' => getVisitasPorHora(),
    'alertas' => $alertas,
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($dados);

$conexao->close();
?>
