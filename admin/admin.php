
<?php
// Garante que $recent_activities seja sempre array
if (!isset($recent_activities) || !is_array($recent_activities)) {
    $recent_activities = [];
}
// Garante que $metrics tenha todas as chaves necess�rias
$metrics_defaults = [
    'total_approved_value' => 0,
    'approval_rate' => 0,
    'total_clients' => 0,
    'new_clients_7d' => 0,
    'total_requests_30d' => 0,
    'conversion_rate' => 0,
    'total_visits_30d' => 0,
    'avg_ticket' => 0,
    'new_clients_30d' => 0,
    'avg_rating' => 0,
    'total_testimonials_30d' => 0,
    'emails_received_30d' => 0
];
if (!isset($metrics) || !is_array($metrics)) {
    $metrics = $metrics_defaults;
} else {
    $metrics = array_merge($metrics_defaults, $metrics);
}
session_start();
include_once('../php/conexao.php');

// DEFINIR HEADERS PARA UTF-8
header('Content-Type: text/html; charset=UTF-8');
header('Content-Language: pt-BR');

// Garantir que o PHP use UTF-8 internamente
ini_set('default_charset', 'UTF-8');
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}
if (function_exists('mb_http_output')) {
    mb_http_output('UTF-8');
}

// Função para garantir UTF-8 nos dados do banco
function ensure_utf8($string) {
    if (is_string($string)) {
        // Verificar se já está em UTF-8
        if (mb_detect_encoding($string, 'UTF-8', true) === false) {
            // Tentar converter de ISO-8859-1 para UTF-8
            $string = mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
        }
        return $string;
    }
    return $string;
}

// Aplicar ensure_utf8 em todos os resultados de queries
function utf8_array_walk_recursive(&$array) {
    if (is_array($array)) {
        array_walk_recursive($array, function(&$value) {
            $value = ensure_utf8($value);
        });
    }
}
?>
<script>
    // Funções globais para o sistema de iframe - definidas no sidebar.php
    // loadInIframe, backToDashboard e adjustIframeHeight estão definidas no sidebar.php
</script>
<?php
$metrics = [];
$financial_query = "
    SELECT
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'aprovado' THEN 1 ELSE 0 END) as approved_requests,
        SUM(CASE WHEN status = 'aprovado' THEN valor_solicitado ELSE 0 END) as total_approved_value,
        AVG(CASE WHEN status = 'aprovado' THEN valor_solicitado ELSE NULL END) as avg_approved_value
    FROM requisicoes
    WHERE data_requisicao >= DATE_SUB(NOW(), INTERVAL 30 DAY)
";
$financial_result = mysqli_query($conexao, $financial_query);
$financial_data = mysqli_fetch_assoc($financial_result);
utf8_array_walk_recursive($financial_data);
$metrics['total_requests_30d'] = $financial_data['total_requests'] ?? 0;
$metrics['approved_requests_30d'] = $financial_data['approved_requests'] ?? 0;
$metrics['approval_rate'] = $metrics['total_requests_30d'] > 0 ?
    round(($metrics['approved_requests_30d'] / $metrics['total_requests_30d']) * 100, 1) : 0;
$metrics['total_approved_value'] = $financial_data['total_approved_value'] ?? 0;
$metrics['avg_ticket'] = $financial_data['avg_approved_value'] ?? 0;


$clients_query = "
    SELECT
        COUNT(*) as total_clients,
        SUM(CASE WHEN data_cadastro >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as new_clients_7d,
        SUM(CASE WHEN data_cadastro >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_clients_30d
    FROM clientes
";
$clients_result = mysqli_query($conexao, $clients_query);
$clients_data = mysqli_fetch_assoc($clients_result);
utf8_array_walk_recursive($clients_data);
$metrics['total_clients'] = $clients_data['total_clients'] ?? 0;
$metrics['new_clients_7d'] = $clients_data['new_clients_7d'] ?? 0;
$metrics['new_clients_30d'] = $clients_data['new_clients_30d'] ?? 0;

// Depoimentos recebidos nos últimos 30 dias
$depoimentos_query = "SELECT COUNT(*) as total_testimonials_30d FROM depoimentos WHERE data_envio >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$depoimentos_result = mysqli_query($conexao, $depoimentos_query);
if ($depoimentos_result) {
    $depoimentos_data = mysqli_fetch_assoc($depoimentos_result);
    utf8_array_walk_recursive($depoimentos_data);
    $metrics['total_testimonials_30d'] = $depoimentos_data['total_testimonials_30d'] ?? 0;
}

$visits_query = "
    SELECT
        COUNT(*) as total_visits_30d,
        COUNT(DISTINCT DATE(data_visita)) as active_days_30d
    FROM contador_visitas
    WHERE data_visita >= DATE_SUB(NOW(), INTERVAL 30 DAY)
";
$visits_result = mysqli_query($conexao, $visits_query);
$visits_data = mysqli_fetch_assoc($visits_result);
utf8_array_walk_recursive($visits_data);
$metrics['total_visits_30d'] = $visits_data['total_visits_30d'] ?? 0;
$metrics['conversion_rate'] = $metrics['total_visits_30d'] > 0 ?
    round(($metrics['total_requests_30d'] / $metrics['total_visits_30d']) * 100, 2) : 0;

// Verificar e criar tabelas necessárias se não existirem
$create_tables_sql = [
    "CREATE TABLE IF NOT EXISTS newsletter (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        data_inscricao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('ativo','inativo') DEFAULT 'ativo'
    )",
    "CREATE TABLE IF NOT EXISTS reset_senha_solicitacoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT,
        email VARCHAR(255) NOT NULL,
        nome_usuario VARCHAR(255),
        data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pendente','processado','cancelado') DEFAULT 'pendente',
        nova_senha VARCHAR(255) NULL,
        data_processamento TIMESTAMP NULL
    )",
    // Adicionar colunas faltantes nas tabelas existentes
    "ALTER TABLE depoimentos ADD COLUMN status ENUM('pendente', 'aprovado', 'reprovado') DEFAULT 'pendente'",
    "ALTER TABLE adm ADD COLUMN avatar VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE usuarios ADD COLUMN status ENUM('ativo', 'inativo') DEFAULT 'ativo'"
];

// Função para verificar se uma coluna existe em uma tabela
function columnExists($conexao, $table, $column) {
    $result = mysqli_query($conexao, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return mysqli_num_rows($result) > 0;
}

// Executar ALTER TABLE apenas se as colunas não existirem
if (!columnExists($conexao, 'depoimentos', 'status')) {
    mysqli_query($conexao, "ALTER TABLE depoimentos ADD COLUMN status ENUM('pendente', 'aprovado', 'reprovado') DEFAULT 'pendente'");
}
if (!columnExists($conexao, 'adm', 'avatar')) {
    mysqli_query($conexao, "ALTER TABLE adm ADD COLUMN avatar VARCHAR(255) DEFAULT NULL");
}
if (!columnExists($conexao, 'usuarios', 'status')) {
    mysqli_query($conexao, "ALTER TABLE usuarios ADD COLUMN status ENUM('ativo', 'inativo') DEFAULT 'ativo'");
}

// Emails recebidos da newsletter nos últimos 30 dias
$newsletter_query = "SELECT COUNT(*) as total_emails_30d FROM newsletter WHERE data_inscricao >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$newsletter_result = mysqli_query($conexao, $newsletter_query);
if ($newsletter_result) {
    $newsletter_data = mysqli_fetch_assoc($newsletter_result);
    $metrics['emails_received_30d'] = $newsletter_data['total_emails_30d'] ?? 0;
} else {
    $metrics['emails_received_30d'] = 0; // Tabela não existe ainda
}

// Buscar depoimentos recentes (últimos 5)
$recent_testimonials_query = "
    SELECT nome_cliente, mensagem, avaliacao, data_envio, aprovado
    FROM depoimentos
    WHERE aprovado = 1
    ORDER BY data_envio DESC
    LIMIT 5
";
$recent_testimonials_result = mysqli_query($conexao, $recent_testimonials_query);
$recent_testimonials = [];
if ($recent_testimonials_result) {
    while ($row = mysqli_fetch_assoc($recent_testimonials_result)) {
        utf8_array_walk_recursive($row);
        $recent_testimonials[] = $row;
    }
}

// Buscar emails recentes da newsletter (últimos 5)
$recent_emails_query = "
    SELECT email, data_inscricao
    FROM newsletter
    WHERE status = 'ativo'
    ORDER BY data_inscricao DESC
    LIMIT 5
";
$recent_emails_result = mysqli_query($conexao, $recent_emails_query);
$recent_emails = [];
if ($recent_emails_result) {
    while ($row = mysqli_fetch_assoc($recent_emails_result)) {
        utf8_array_walk_recursive($row);
        $recent_emails[] = $row;
    }
} else {
    $recent_emails = []; // Tabela não existe ainda
}

// Calcular avalia��o m�dia dos depoimentos
$avg_rating_query = "SELECT AVG(avaliacao) as avg_rating FROM depoimentos WHERE aprovado = 1";
$avg_rating_result = mysqli_query($conexao, $avg_rating_query);
if ($avg_rating_result) {
    $avg_rating_data = mysqli_fetch_assoc($avg_rating_result);
    utf8_array_walk_recursive($avg_rating_data);
    $metrics['avg_rating'] = round($avg_rating_data['avg_rating'] ?? 0, 1);
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Executivo - CONFINTER</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@1.13.1/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="content-wrapper" style="background: transparent; min-height: 100vh;">
        <section class="content pt-2">
            <div class="container-fluid">
                <!-- Iframe Container -->
                <div id="iframe-container" style="display: none; height: calc(100vh - 60px); overflow: hidden;">
                    <div class="row mb-2">
                        <div class="col-12">
                            <button id="back-to-dashboard" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left mr-1"></i>Voltar ao Dashboard
                            </button>
                            <h4 id="iframe-title" class="d-inline ml-2 mb-0"></h4>
                        </div>
                    </div>
                    <div class="row" style="height: calc(100% - 50px);">
                        <div class="col-12" style="height: 100%; padding: 0;">
                            <div class="card" style="height: 100%; border: none; box-shadow: none;">
                                <div class="card-body" style="height: 100%; padding: 0;">
                                    <iframe id="content-iframe" src="" style="width: 100%; height: 100%; border: none; min-height: 600px;" onload="adjustIframeHeight()"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Content -->
                <div id="dashboard-content">
                    <!-- T�tulo -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <h2 class="mb-0"><i class="fas fa-chart-line mr-2"></i>Dashboard Executivo</h2>
                            <p class="text-muted">Visão geral completa do desempenho da CONFINTER</p>
                        </div>
                    </div>
                <!-- Info boxes -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success" onclick="loadInIframe('relatorios.php', 'Relat�rios Financeiros')" style="cursor: pointer;">
                            <div class="inner">
                                <h3>R$ <?php echo number_format($metrics['total_approved_value'], 0, ',', '.'); ?></h3>
                                <p>Receita Aprovada (30d)</p>
                            </div>
                            <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                            <span class="small-box-footer">Taxa de Aprovação: <?php echo $metrics['approval_rate']; ?>% <i class="fas fa-arrow-up"></i></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary" onclick="loadInIframe('listaclientes.php', 'Lista de Clientes')" style="cursor: pointer;">
                            <div class="inner">
                                <h3><?php echo number_format($metrics['total_clients'], 0, ',', '.'); ?></h3>
                                <p>Total de Clientes</p>
                            </div>
                            <div class="icon"><i class="fas fa-users"></i></div>
                            <span class="small-box-footer">+<?php echo $metrics['new_clients_7d']; ?> novos (7d) <i class="fas fa-user-plus"></i></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning" onclick="loadInIframe('requisicoes.php', 'Requisições de Crédito')" style="cursor: pointer;">
                            <div class="inner">
                                <h3><?php echo number_format($metrics['total_requests_30d'], 0, ',', '.'); ?></h3>
                                <p>Requisições (30d)</p>
                            </div>
                            <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                            <span class="small-box-footer"><?php echo isset($pipeline['pendente']['count']) ? $pipeline['pendente']['count'] : 0; ?> pendentes <i class="fas fa-clock"></i></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger" onclick="loadInIframe('contador.php', 'Estat�sticas de Acesso')" style="cursor: pointer;">
                            <div class="inner">
                                <h3><?php echo number_format($metrics['conversion_rate'], 1, ',', '.'); ?>%</h3>
                                <p>Conversão do Site</p>
                            </div>
                            <div class="icon"><i class="fas fa-chart-pie"></i></div>
                            <span class="small-box-footer"><?php echo number_format($metrics['total_visits_30d'], 0, ',', '.'); ?> visitas <i class="fas fa-eye"></i></span>
                        </div>
                    </div>
                </div>

                <!-- Segunda linha de cards - Depoimentos e Emails -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info" onclick="loadInIframe('mod_depoimentos.php', 'Moderar Depoimentos')" style="cursor: pointer;">
                            <div class="inner">
                                <h3><?php echo number_format($metrics['total_testimonials_30d'], 0, ',', '.'); ?></h3>
                                <p>Depoimentos (30d)</p>
                            </div>
                            <div class="icon"><i class="fas fa-comments"></i></div>
                            <span class="small-box-footer">Avaliações recebidas <i class="fas fa-star"></i></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-secondary" onclick="loadInIframe('newsletter.php', 'Newsletter')" style="cursor: pointer;">
                            <div class="inner">
                                <h3><?php echo number_format($metrics['emails_received_30d'], 0, ',', '.'); ?></h3>
                                <p>Emails Cadastrados (30d)</p>
                            </div>
                            <div class="icon"><i class="fas fa-envelope"></i></div>
                            <span class="small-box-footer">Newsletter subscribers <i class="fas fa-at"></i></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-light" onclick="loadInIframe('mod_depoimentos.php', 'Avalia��es')" style="cursor: pointer;">
                            <div class="inner">
                                <h3><?php echo number_format($metrics['avg_rating'], 1, ',', '.'); ?></h3>
                                <p>Avaliação Média</p>
                            </div>
                            <div class="icon"><i class="fas fa-star text-warning"></i></div>
                            <span class="small-box-footer">Satisfação dos clientes <i class="fas fa-heart"></i></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-dark" onclick="loadInIframe('relatorios.php', 'Relatórios Gerais')" style="cursor: pointer;">
                            <div class="inner">
                                <h3><?php echo number_format($metrics['total_testimonials_30d'] + $metrics['emails_received_30d'], 0, ',', '.'); ?></h3>
                                <p>Interações (30d)</p>
                            </div>
                            <div class="icon"><i class="fas fa-handshake"></i></div>
                            <span class="small-box-footer">Engajamento total <i class="fas fa-users"></i></span>
                        </div>
                    </div>
                </div>
                <!-- Gráficos e Atividades -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Pipeline de Vendas (30d)</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="pipelineChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-history mr-2"></i>Atividades Recentes</h3>
                            </div>
                            <div class="card-body" style="max-height: 320px; overflow-y: auto;">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($recent_activities as $activity): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            <?php
                                            $icon = $activity['tipo'] === 'requisi��o' ? 'clipboard-list' : 'user-plus';
                                            $color = $activity['tipo'] === 'requisi��o' ? 'primary' : 'success';
                                            ?>
                                            <i class="fas fa-<?php echo $icon; ?> text-<?php echo $color; ?> mr-2"></i>
                                            <?php echo htmlspecialchars($activity['descricao']); ?>
                                            <br>
                                            <small class="text-muted"><i class="fas fa-clock mr-1"></i><?php echo date('d/m/Y H:i', strtotime($activity['data_hora'])); ?></small>
                                        </span>
                                        <span class="badge badge-<?php
                                            $status_class = 'secondary';
                                            if ($activity['status'] === 'aprovado') $status_class = 'success';
                                            elseif ($activity['status'] === 'reprovado') $status_class = 'danger';
                                            elseif ($activity['status'] === 'pendente') $status_class = 'warning';
                                            elseif ($activity['status'] === 'ativo') $status_class = 'success';
                                            echo $status_class;
                                        ?> badge-pill"><?php echo ucfirst($activity['status']); ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mais Gráficos -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Vendas por Semana</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="salesChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Distribuição por Tipo de Crédito</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="creditTypeChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Conversões -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-area mr-2"></i>Conversões do Site (30d)</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="conversionChart" width="100%" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Depoimentos e Emails Recentes -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-comments mr-2"></i>Depoimentos Recentes</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-success btn-sm" onclick="loadInIframe('mod_depoimentos.php', 'Moderar Depoimentos')">
                                        <i class="fas fa-eye"></i> Ver Todos
                                    </button>
                                </div>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                <?php if (empty($recent_testimonials)): ?>
                                    <div class="text-center text-muted">
                                        <i class="fas fa-comments fa-3x mb-3"></i>
                                        <p>Nenhum depoimento aprovado ainda.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="timeline timeline-inverse">
                                        <?php foreach ($recent_testimonials as $testimonial): ?>
                                        <div class="time-label">
                                            <span class="bg-success"><?php echo date('d/m/Y', strtotime($testimonial['data_envio'])); ?></span>
                                        </div>
                                        <div>
                                            <i class="fas fa-comments bg-info"></i>
                                            <div class="timeline-item">
                                                <span class="time"><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($testimonial['data_envio'])); ?></span>
                                                <h3 class="timeline-header">
                                                    <strong><?php echo htmlspecialchars($testimonial['nome_cliente']); ?></strong>
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $testimonial['avaliacao'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                    <?php endfor; ?>
                                                </h3>
                                                <div class="timeline-body">
                                                    <?php echo htmlspecialchars(substr($testimonial['mensagem'], 0, 150)); ?>
                                                    <?php if (strlen($testimonial['mensagem']) > 150): ?>...<?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-envelope mr-2"></i>Emails Cadastrados Recentes</h3>
                                <div class="card-tools">
                                    <a href="newsletter.php" class="btn btn-warning btn-sm">
                                        <i class="fas fa-eye"></i> Ver Todos
                                    </a>
                                </div>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                <?php if (empty($recent_emails)): ?>
                                    <div class="text-center text-muted">
                                        <i class="fas fa-envelope fa-3x mb-3"></i>
                                        <p>Nenhum email cadastrado ainda.</p>
                                    </div>
                                <?php else: ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($recent_emails as $email): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-envelope text-warning mr-2"></i>
                                                <strong><?php echo htmlspecialchars($email['email']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar mr-1"></i>
                                                    Cadastrado em <?php echo date('d/m/Y H:i', strtotime($email['data_inscricao'])); ?>
                                                </small>
                                            </div>
                                            <span class="badge badge-success badge-pill">
                                                <i class="fas fa-check"></i> Ativo
                                            </span>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-eye"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Acessos ao Site (30d)</span>
                                <span class="info-box-number"><?php echo number_format($metrics['total_visits_30d'], 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Dias com Acesso (30d)</span>
                                <span class="info-box-number"><?php echo isset($visits_data['active_days_30d']) ? $visits_data['active_days_30d'] : 0; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-envelope"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">E-mails Recebidos (30d)</span>
                                <span class="info-box-number"><?php echo isset($metrics['emails_received_30d']) ? $metrics['emails_received_30d'] : 0; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-comments"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Depoimentos Recebidos (30d)</span>
                                <span class="info-box-number"><?php echo isset($metrics['total_testimonials_30d']) && is_numeric($metrics['total_testimonials_30d']) ? $metrics['total_testimonials_30d'] : 0; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                    <div class="col-12 text-right text-muted">
                        <small>Última atualização: <?php echo date('d/m/Y H:i'); ?> | Usuário: <?php echo htmlspecialchars($_SESSION['usuario']); ?> | Perfil: <?php echo htmlspecialchars($_SESSION['perfil'] ?? 'admin'); ?> | Status: <span class="badge badge-success">Online</span></small>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php include 'footer.php'; ?>
</div>

<script>
    $(document).ready(function() {
        // Botão voltar
        $('#back-to-dashboard').click(function() {
            backToDashboard();
        });

        // Verificar se há parâmetro de página na URL
        var urlParams = new URLSearchParams(window.location.search);
        var pageParam = urlParams.get('page');
        if (pageParam) {
            var title = 'Página Carregada';
            if (pageParam.includes('listarusuario')) title = 'Listar Usuários';
            else if (pageParam.includes('novousuario')) title = 'Novo Usuário';
            else if (pageParam.includes('perfil')) title = 'Meu Perfil';
            else if (pageParam.includes('reset_senha')) title = 'Reset de Senha';
            else if (pageParam.includes('buscar_empresa')) title = 'Buscar Clientes';
            else if (pageParam.includes('clientedit')) title = 'Editar Cliente';
            else if (pageParam.includes('requisicoes')) title = 'Requisições';
            else if (pageParam.includes('mod_depoimentos')) title = 'Depoimentos';
            else if (pageParam.includes('upload_imagens')) title = 'Upload de Imagens';
            else if (pageParam.includes('galeria')) title = 'Galeria';
            else if (pageParam.includes('contador')) title = 'Estatísticas';
            else if (pageParam.includes('relatorios')) title = 'Relatórios';
            else if (pageParam.includes('configuracoes')) title = 'Configurações';
            else if (pageParam.includes('backup')) title = 'Backup';

            loadInIframe(pageParam, title);
        }

        // Suporte ao botão voltar do navegador
        window.addEventListener('popstate', function(event) {
            if (event.state && event.state.iframe) {
                loadInIframe(event.state.url, event.state.title);
            } else {
                backToDashboard();
            }
        });
    });
// Inicializar gráficos do dashboard
initializeCharts();
// Função para inicializar os gráficos
function initializeCharts() {
    // Gráfico de Pipeline de Vendas (Doughnut)
    const pipelineCtx = document.getElementById('pipelineChart').getContext('2d');
    new Chart(pipelineCtx, {
        type: 'doughnut',
        data: {
            labels: ['Aprovadas', 'Pendentes', 'Rejeitadas'],
            datasets: [{
                data: [<?php echo $approved_requests; ?>, <?php echo $pending_requests; ?>, <?php echo $rejected_requests; ?>],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Vendas por Semana (Line)
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'],
            datasets: [{
                label: 'Vendas',
                data: [<?php echo $sales_week1; ?>, <?php echo $sales_week2; ?>, <?php echo $sales_week3; ?>, <?php echo $sales_week4; ?>],
                borderColor: 'rgba(0, 123, 255, 1)',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Distribuição por Tipo de Crédito (Doughnut)
    const creditCtx = document.getElementById('creditTypeChart').getContext('2d');
    new Chart(creditCtx, {
        type: 'doughnut',
        data: {
            labels: ['Crédito Pessoal', 'Crédito Empresarial', 'Crédito Consignado'],
            datasets: [{
                data: [<?php echo $personal_credit; ?>, <?php echo $business_credit; ?>, <?php echo $consigned_credit; ?>],
                backgroundColor: [
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(108, 117, 125, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderColor: [
                    'rgba(23, 162, 184, 1)',
                    'rgba(108, 117, 125, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Conversões do Site (Line)
    const conversionCtx = document.getElementById('conversionChart').getContext('2d');
    new Chart(conversionCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
            datasets: [{
                label: 'Taxa de Convers�o (%)',
                data: [<?php echo $conversion_jan; ?>, <?php echo $conversion_feb; ?>, <?php echo $conversion_mar; ?>, <?php echo $conversion_apr; ?>, <?php echo $conversion_may; ?>, <?php echo $conversion_jun; ?>],
                borderColor: 'rgba(40, 167, 69, 1)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
}
</script>

</body>
</html>
