<?php
session_start();
include_once('../php/conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// =====================================================
// DASHBOARD EXECUTIVO MODERNO - CONFINTER
// =====================================================

// Buscar métricas atualizadas
$metrics = [];

// 1. PERFORMANCE FINANCEIRA (últimos 30 dias)
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

$metrics['total_requests_30d'] = $financial_data['total_requests'] ?? 0;
$metrics['approved_requests_30d'] = $financial_data['approved_requests'] ?? 0;
$metrics['approval_rate'] = $metrics['total_requests_30d'] > 0 ?
    round(($metrics['approved_requests_30d'] / $metrics['total_requests_30d']) * 100, 1) : 0;
$metrics['total_approved_value'] = $financial_data['total_approved_value'] ?? 0;
$metrics['avg_ticket'] = $financial_data['avg_approved_value'] ?? 0;

// 2. MÉTRICAS DE CLIENTES
$clients_query = "
    SELECT
        COUNT(*) as total_clients,
        SUM(CASE WHEN data_cadastro >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as new_clients_7d,
        SUM(CASE WHEN data_cadastro >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_clients_30d
    FROM clientes
";
$clients_result = mysqli_query($conexao, $clients_query);
$clients_data = mysqli_fetch_assoc($clients_result);

$metrics['total_clients'] = $clients_data['total_clients'] ?? 0;
$metrics['new_clients_7d'] = $clients_data['new_clients_7d'] ?? 0;
$metrics['new_clients_30d'] = $clients_data['new_clients_30d'] ?? 0;

// 3. VISITAS E CONVERSÃO
$visits_query = "
    SELECT
        COUNT(*) as total_visits_30d,
        COUNT(DISTINCT DATE(data_visita)) as active_days_30d
    FROM contador_visitas
    WHERE data_visita >= DATE_SUB(NOW(), INTERVAL 30 DAY)
";
$visits_result = mysqli_query($conexao, $visits_query);
$visits_data = mysqli_fetch_assoc($visits_result);

$metrics['total_visits_30d'] = $visits_data['total_visits_30d'] ?? 0;
$metrics['conversion_rate'] = $metrics['total_visits_30d'] > 0 ?
    round(($metrics['total_requests_30d'] / $metrics['total_visits_30d']) * 100, 2) : 0;

// 4. PIPELINE DE VENDAS
$pipeline_query = "
    SELECT status, COUNT(*) as count, SUM(valor_solicitado) as value
    FROM requisicoes
    WHERE data_requisicao >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY status
";
$pipeline_result = mysqli_query($conexao, $pipeline_query);
$pipeline = [];
while ($row = mysqli_fetch_assoc($pipeline_result)) {
    $pipeline[$row['status']] = [
        'count' => $row['count'],
        'value' => $row['value'] ?? 0
    ];
}

// 5. DEPOIMENTOS
$testimonials_query = "
    SELECT
        COUNT(*) as total_testimonials,
        AVG(avaliacao) as avg_rating,
        SUM(CASE WHEN aprovado = 1 THEN 1 ELSE 0 END) as approved_testimonials
    FROM depoimentos
    WHERE data_envio >= DATE_SUB(NOW(), INTERVAL 30 DAY)
";
$testimonials_result = mysqli_query($conexao, $testimonials_query);
$testimonials_data = mysqli_fetch_assoc($testimonials_result);

$metrics['total_testimonials_30d'] = $testimonials_data['total_testimonials'] ?? 0;
$metrics['avg_rating'] = $testimonials_data['avg_rating'] ?? 0;
$metrics['approved_testimonials'] = $testimonials_data['approved_testimonials'] ?? 0;

// 6. ATIVIDADES RECENTES
$recent_activities = [];
$activities_query = "
    (SELECT 'requisição' as tipo, CONCAT('R$ ', FORMAT(r.valor_solicitado, 2, 'pt_BR'), ' - ', c.nome) as descricao,
            r.data_requisicao as data_hora, r.status
     FROM requisicoes r
     JOIN clientes c ON r.id_cliente = c.id_cliente
     ORDER BY r.data_requisicao DESC LIMIT 5)
    UNION ALL
    (SELECT 'cliente' as tipo, CONCAT('Novo cliente: ', nome) as descricao,
            data_cadastro as data_hora, 'ativo' as status
     FROM clientes
     ORDER BY data_cadastro DESC LIMIT 3)
    ORDER BY data_hora DESC
    LIMIT 8
";
$activities_result = mysqli_query($conexao, $activities_query);
while ($activity = mysqli_fetch_assoc($activities_result)) {
    $recent_activities[] = $activity;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Executivo - CONFINTER</title>

    <!-- CSS Libraries (AdminLTE 3 + Bootstrap 4) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary-color: #007bff;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --dark-color: #343a40;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 8px;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .metric-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 14px 10px 12px 10px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.07);
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
            backdrop-filter: blur(8px);
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.10);
        }

        .metric-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 8px;
        }

        .metric-value {
            font-size: 1.7rem;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .metric-label {
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-change {
            font-size: 0.75rem;
            margin-top: 4px;
        }

        .chart-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 14px 10px 12px 10px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.07);
            backdrop-filter: blur(8px);
        }

        .activity-item {
            padding: 8px 10px;
            border-left: 4px solid var(--primary-color);
            margin-bottom: 6px;
            background: rgba(255,255,255,0.8);
            border-radius: 8px;
            transition: all 0.2s;
        }

        .activity-item:hover {
            background: rgba(255,255,255,0.9);
            transform: translateX(2px);
        }

        .status-badge {
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 18px 8px 12px 8px;
            border-radius: 12px;
            margin-bottom: 12px;
            text-align: center;
        }

        .welcome-section h1 {
            font-size: 1.5rem;
            margin-bottom: 6px;
        }

        .welcome-section p {
            font-size: 1rem;
            opacity: 0.9;
        }

        .row {
            margin-left: -4px;
            margin-right: -4px;
        }
        [class^='col-'] {
            padding-left: 4px;
            padding-right: 4px;
        }

        @media (max-width: 1200px) {
            .metric-value { font-size: 1.2rem; }
            .metric-icon { width: 36px; height: 36px; font-size: 16px; }
        }
        @media (max-width: 992px) {
            .metric-value { font-size: 1.1rem; }
            .metric-icon { width: 32px; height: 32px; font-size: 15px; }
        }
        @media (max-width: 768px) {
            .metric-card { margin-bottom: 8px; }
            .welcome-section { padding: 10px 4px 8px 4px; }
            .welcome-section h1 { font-size: 1.1rem; }
        }
    </style>
</head>
<body>

<div class="dashboard-container">

    <!-- Welcome Section -->
    <div class="welcome-section">
        <h1><i class="fas fa-chart-line me-3"></i>Dashboard Executivo</h1>
        <p>Visão geral completa do desempenho da CONFINTER</p>
        <div class="row mt-4">
            <div class="col-md-3">
                <small>Última atualização: <?php echo date('d/m/Y H:i'); ?></small>
            </div>
            <div class="col-md-3">
                <small>Usuário: <?php echo htmlspecialchars($_SESSION['usuario']); ?></small>
            </div>
            <div class="col-md-3">
                <small>Perfil: <?php echo htmlspecialchars($_SESSION['perfil'] ?? 'admin'); ?></small>
            </div>
            <div class="col-md-3">
                <small>Status: <span class="badge bg-success">Online</span></small>
            </div>
        </div>
    </div>

    <!-- Métricas Principais -->
    <div class="row">
        <!-- Receita Aprovada -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="metric-card">
                <div class="metric-icon" style="background: linear-gradient(45deg, #28a745, #20c997); color: white;">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="metric-value text-success">
                    R$ <?php echo number_format($metrics['total_approved_value'], 0, ',', '.'); ?>
                </div>
                <div class="metric-label">Receita Aprovada (30d)</div>
                <div class="metric-change">
                    <span class="text-success">
                        <i class="fas fa-arrow-up"></i>
                        <?php echo $metrics['approval_rate']; ?>% Taxa de Aprovação
                    </span>
                </div>
            </div>
        </div>

        <!-- Total de Clientes -->
    <div class="col-12 col-sm-6 col-md-3">
            <div class="metric-card">
                <div class="metric-icon" style="background: linear-gradient(45deg, #007bff, #6610f2); color: white;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="metric-value text-primary">
                    <?php echo number_format($metrics['total_clients'], 0, ',', '.'); ?>
                </div>
                <div class="metric-label">Total de Clientes</div>
                <div class="metric-change">
                    <span class="text-info">
                        <i class="fas fa-plus-circle"></i>
                        +<?php echo $metrics['new_clients_7d']; ?> novos (7d)
                    </span>
                </div>
            </div>
        </div>

        <!-- Requisições -->
    <div class="col-12 col-sm-6 col-md-3">
            <div class="metric-card">
                <div class="metric-icon" style="background: linear-gradient(45deg, #ffc107, #fd7e14); color: white;">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="metric-value text-warning">
                    <?php echo number_format($metrics['total_requests_30d'], 0, ',', '.'); ?>
                </div>
                <div class="metric-label">Requisições (30d)</div>
                <div class="metric-change">
                    <span class="text-warning">
                        <i class="fas fa-clock"></i>
                        <?php echo isset($pipeline['pendente']['count']) ? $pipeline['pendente']['count'] : 0; ?> pendentes
                    </span>
                </div>
            </div>
        </div>

        <!-- Taxa de Conversão -->
    <div class="col-12 col-sm-6 col-md-3">
            <div class="metric-card">
                <div class="metric-icon" style="background: linear-gradient(45deg, #dc3545, #e83e8c); color: white;">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div class="metric-value text-danger">
                    <?php echo number_format($metrics['conversion_rate'], 1, ',', '.'); ?>%
                </div>
                <div class="metric-label">Conversão do Site</div>
                <div class="metric-change">
                    <span class="text-muted">
                        <i class="fas fa-eye"></i>
                        <?php echo number_format($metrics['total_visits_30d'], 0, ',', '.'); ?> visitas
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos e Análises -->
    <div class="row">
        <!-- Pipeline de Vendas -->
    <div class="col-12 col-md-6">
            <div class="chart-container">
                <h4 class="mb-4"><i class="fas fa-chart-bar me-2"></i>Pipeline de Vendas (30d)</h4>
                <canvas id="pipelineChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Atividades Recentes -->
    <div class="col-12 col-md-6">
            <div class="chart-container">
                <h4 class="mb-4"><i class="fas fa-history me-2"></i>Atividades Recentes</h4>
                <div class="activity-list">
                    <?php foreach ($recent_activities as $activity): ?>
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-fill">
                                <div class="fw-bold">
                                    <?php
                                    $icon = $activity['tipo'] === 'requisição' ? 'clipboard-list' : 'user-plus';
                                    $color = $activity['tipo'] === 'requisição' ? 'primary' : 'success';
                                    ?>
                                    <i class="fas fa-<?php echo $icon; ?> text-<?php echo $color; ?> me-2"></i>
                                    <?php echo htmlspecialchars($activity['descricao']); ?>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($activity['data_hora'])); ?>
                                </small>
                            </div>
                            <div>
                                <?php
                                $status_class = 'secondary';
                                if ($activity['status'] === 'aprovado') $status_class = 'success';
                                elseif ($activity['status'] === 'reprovado') $status_class = 'danger';
                                elseif ($activity['status'] === 'pendente') $status_class = 'warning';
                                elseif ($activity['status'] === 'ativo') $status_class = 'success';
                                ?>
                                <span class="status-badge bg-<?php echo $status_class; ?>">
                                    <?php echo ucfirst($activity['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas Adicionais -->
    <div class="row">
        <!-- Ticket Médio -->
    <div class="col-12 col-md-4">
            <div class="metric-card text-center">
                <div class="metric-icon mx-auto" style="background: linear-gradient(45deg, #17a2b8, #20c997); color: white;">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="metric-value text-info">
                    R$ <?php echo number_format($metrics['avg_ticket'], 0, ',', '.'); ?>
                </div>
                <div class="metric-label">Ticket Médio Aprovado</div>
            </div>
        </div>

        <!-- Novos Clientes -->
    <div class="col-12 col-md-4">
            <div class="metric-card text-center">
                <div class="metric-icon mx-auto" style="background: linear-gradient(45deg, #6f42c1, #e83e8c); color: white;">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="metric-value text-purple">
                    +<?php echo $metrics['new_clients_30d']; ?>
                </div>
                <div class="metric-label">Novos Clientes (30d)</div>
            </div>
        </div>

        <!-- Depoimentos -->
    <div class="col-12 col-md-4">
            <div class="metric-card text-center">
                <div class="metric-icon mx-auto" style="background: linear-gradient(45deg, #fd7e14, #ffc107); color: white;">
                    <i class="fas fa-star"></i>
                </div>
                <div class="metric-value text-warning">
                    <?php echo number_format($metrics['avg_rating'], 1, ',', '.'); ?>
                </div>
                <div class="metric-label">Avaliação Média</div>
                <div class="metric-change">
                    <span class="text-warning">
                        <i class="fas fa-comments"></i>
                        <?php echo $metrics['total_testimonials_30d']; ?> depoimentos (30d)
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js Scripts -->
<!-- Required Scripts (match AdminLTE 3) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
// Pipeline Chart
const pipelineCtx = document.getElementById('pipelineChart').getContext('2d');
const pipelineData = {
    labels: ['Aprovado', 'Pendente', 'Reprovado', 'Em Análise'],
    datasets: [{
        label: 'Quantidade',
        data: [
            <?php echo isset($pipeline['aprovado']['count']) ? $pipeline['aprovado']['count'] : 0; ?>,
            <?php echo isset($pipeline['pendente']['count']) ? $pipeline['pendente']['count'] : 0; ?>,
            <?php echo isset($pipeline['reprovado']['count']) ? $pipeline['reprovado']['count'] : 0; ?>,
            <?php echo isset($pipeline['em_analise']['count']) ? $pipeline['em_analise']['count'] : 0; ?>
        ],
        backgroundColor: [
            'rgba(40, 167, 69, 0.8)',
            'rgba(255, 193, 7, 0.8)',
            'rgba(220, 53, 69, 0.8)',
            'rgba(23, 162, 184, 0.8)'
        ],
        borderColor: [
            'rgba(40, 167, 69, 1)',
            'rgba(255, 193, 7, 1)',
            'rgba(220, 53, 69, 1)',
            'rgba(23, 162, 184, 1)'
        ],
        borderWidth: 2
    }]
};

const pipelineChart = new Chart(pipelineCtx, {
    type: 'doughnut',
    data: pipelineData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
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
        },
        animation: {
            animateScale: true,
            animateRotate: true
        }
    }
});

// Auto-refresh do dashboard a cada 5 minutos
setInterval(function() {
    location.reload();
}, 300000);

</script>

</body>
</html>
