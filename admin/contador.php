<?php
session_start();
include_once('../php/conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Detectar se está em iframe
$is_iframe = isset($_GET['iframe']) && ($_GET['iframe'] == '1' || $_GET['iframe'] == 'true');

// Buscar estatísticas de visitas
$stats = [
    'total_visitas' => 0,
    'visitas_hoje' => 0,
    'visitas_semana' => 0,
    'visitas_mes' => 0,
    'visitas_ano' => 0,
    'media_diaria' => 0,
    'top_paginas' => [],
    'visitas_por_mes' => []
];

// Total de visitas
$stmt = $conexao->prepare('SELECT COUNT(*) as total FROM contador_visitas');
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['total_visitas'] = $row['total'];
}
$stmt->close();

// Visitas hoje
$stmt = $conexao->prepare('SELECT COUNT(*) as total FROM contador_visitas WHERE DATE(data_visita) = CURDATE()');
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['visitas_hoje'] = $row['total'];
}
$stmt->close();

// Visitas esta semana
$stmt = $conexao->prepare('SELECT COUNT(*) as total FROM contador_visitas WHERE YEARWEEK(data_visita) = YEARWEEK(CURDATE())');
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['visitas_semana'] = $row['total'];
}
$stmt->close();

// Visitas este mês
$stmt = $conexao->prepare('SELECT COUNT(*) as total FROM contador_visitas WHERE MONTH(data_visita) = MONTH(CURDATE()) AND YEAR(data_visita) = YEAR(CURDATE())');
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['visitas_mes'] = $row['total'];
}
$stmt->close();

// Visitas este ano
$stmt = $conexao->prepare('SELECT COUNT(*) as total FROM contador_visitas WHERE YEAR(data_visita) = YEAR(CURDATE())');
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['visitas_ano'] = $row['total'];
}
$stmt->close();

// Média diária (últimos 30 dias)
$stmt = $conexao->prepare('SELECT COUNT(*) / 30 as media FROM contador_visitas WHERE data_visita >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['media_diaria'] = round($row['media'], 1);
}
$stmt->close();

// Top páginas mais visitadas
$stmt = $conexao->prepare('SELECT pagina, COUNT(*) as visitas FROM contador_visitas GROUP BY pagina ORDER BY visitas DESC LIMIT 10');
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $stats['top_paginas'][] = $row;
}
$stmt->close();

// Visitas por mês (últimos 12 meses)
$stmt = $conexao->prepare('SELECT DATE_FORMAT(data_visita, "%Y-%m") as mes, COUNT(*) as visitas FROM contador_visitas WHERE data_visita >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY mes ORDER BY mes');
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $stats['visitas_por_mes'][] = $row;
}
$stmt->close();

if (!$is_iframe) {
    // Versão completa com navbar e sidebar
?>
<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Contador de Visitas - CONFINTER</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback'>
    <!-- Font Awesome -->
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <!-- Theme style -->
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css'>
    <!-- Custom Admin CSS -->
    <link rel='stylesheet' href='assets/css/custom-admin.css'>
</head>
<body class='hold-transition sidebar-mini layout-fixed'>
<?php
    include 'navbar.php';
    include 'sidebar.php';
?>
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <!-- Cards de Estatísticas -->
            <div class='row'>
                <div class='col-lg-3 col-6'>
                    <div class='small-box bg-info'>
                        <div class='inner'>
                            <h3><?php echo number_format($stats['total_visitas']); ?></h3>
                            <p>Total de Visitas</p>
                        </div>
                        <div class='icon'>
                            <i class='fas fa-eye'></i>
                        </div>
                        <a href='#' class='small-box-footer'>Mais info <i class='fas fa-arrow-circle-right'></i></a>
                    </div>
                </div>

                <div class='col-lg-3 col-6'>
                    <div class='small-box bg-success'>
                        <div class='inner'>
                            <h3><?php echo number_format($stats['visitas_hoje']); ?></h3>
                            <p>Visitas Hoje</p>
                        </div>
                        <div class='icon'>
                            <i class='fas fa-calendar-day'></i>
                        </div>
                        <a href='#' class='small-box-footer'>Mais info <i class='fas fa-arrow-circle-right'></i></a>
                    </div>
                </div>

                <div class='col-lg-3 col-6'>
                    <div class='small-box bg-warning'>
                        <div class='inner'>
                            <h3><?php echo number_format($stats['visitas_semana']); ?></h3>
                            <p>Esta Semana</p>
                        </div>
                        <div class='icon'>
                            <i class='fas fa-calendar-week'></i>
                        </div>
                        <a href='#' class='small-box-footer'>Mais info <i class='fas fa-arrow-circle-right'></i></a>
                    </div>
                </div>

                <div class='col-lg-3 col-6'>
                    <div class='small-box bg-danger'>
                        <div class='inner'>
                            <h3><?php echo number_format($stats['visitas_mes']); ?></h3>
                            <p>Este Mês</p>
                        </div>
                        <div class='icon'>
                            <i class='fas fa-calendar-alt'></i>
                        </div>
                        <a href='#' class='small-box-footer'>Mais info <i class='fas fa-arrow-circle-right'></i></a>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class='row'>
                <!-- Gráfico de Visitas por Mês -->
                <div class='col-md-8'>
                    <div class='card card-primary'>
                        <div class='card-header'>
                            <h3 class='card-title'>
                                <i class='fas fa-chart-line mr-1'></i>
                                Visitas por Mês (Últimos 12 meses)
                            </h3>
                        </div>
                        <div class='card-body'>
                            <canvas id='visitasChart' style='height: 300px;'></canvas>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas Adicionais -->
                <div class='col-md-4'>
                    <div class='card card-info'>
                        <div class='card-header'>
                            <h3 class='card-title'>
                                <i class='fas fa-chart-bar mr-1'></i>
                                Estatísticas
                            </h3>
                        </div>
                        <div class='card-body'>
                            <div class='row'>
                                <div class='col-12'>
                                    <div class='info-box bg-light'>
                                        <span class='info-box-icon'><i class='fas fa-calendar'></i></span>
                                        <div class='info-box-content'>
                                            <span class='info-box-text'>Este Ano</span>
                                            <span class='info-box-number'><?php echo number_format($stats['visitas_ano']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class='col-12'>
                                    <div class='info-box bg-light'>
                                        <span class='info-box-icon'><i class='fas fa-chart-line'></i></span>
                                        <div class='info-box-content'>
                                            <span class='info-box-text'>Média Diária</span>
                                            <span class='info-box-number'><?php echo $stats['media_diaria']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Páginas -->
            <div class='row'>
                <div class='col-md-12'>
                    <div class='card card-success'>
                        <div class='card-header'>
                            <h3 class='card-title'>
                                <i class='fas fa-trophy mr-1'></i>
                                Páginas Mais Visitadas
                            </h3>
                        </div>
                        <div class='card-body table-responsive p-0'>
                            <table class='table table-hover text-nowrap'>
                                <thead>
                                    <tr>
                                        <th>Página</th>
                                        <th>Visitas</th>
                                        <th>Percentual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_visitas = $stats['total_visitas'];
                                    foreach ($stats['top_paginas'] as $pagina): 
                                        $percentual = $total_visitas > 0 ? round(($pagina['visitas'] / $total_visitas) * 100, 1) : 0;
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($pagina['pagina']); ?></td>
                                            <td><?php echo number_format($pagina['visitas']); ?></td>
                                            <td>
                                                <div class='progress progress-xs'>
                                                    <div class='progress-bar bg-success' style='width: <?php echo $percentual; ?>%'></div>
                                                </div>
                                                <span class='badge bg-success'><?php echo $percentual; ?>%</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>

</div>

<!-- jQuery -->
<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
<!-- Bootstrap 4 -->
<script src='https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js'></script>
<!-- AdminLTE App -->
<script src='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js'></script>
<!-- Chart.js -->
<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>

<script>
$(document).ready(function() {
    // Dados para o gráfico
    var visitasData = <?php echo json_encode($stats['visitas_por_mes']); ?>;
    
    var labels = visitasData.map(function(item) {
        var date = new Date(item.mes + '-01');
        return date.toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' });
    });
    
    var data = visitasData.map(function(item) {
        return item.visitas;
    });

    // Criar gráfico
    var ctx = document.getElementById('visitasChart').getContext('2d');
    var visitasChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Visitas',
                data: data,
                borderColor: 'rgba(0, 123, 255, 1)',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('pt-BR');
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Visitas: ' + context.parsed.y.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });
});
</script>

</body>
</html>
<?php } else { ?>
<!-- Versão Iframe -->
<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Contador de Visitas - CONFINTER</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <style>
        body { background: #f4f6f9; margin: 0; padding: 20px; }
        .content-wrapper { margin: 0; background: transparent; }
        .card { box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
    </style>
</head>
<body>
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-chart-line mr-2"></i>
                        Contador de Visitas
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#" onclick="window.top.loadPage('admin.php')">Dashboard</a></li>
                        <li class="breadcrumb-item active">Contador</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Cards de Estatísticas -->
            <div class='row'>
                <div class='col-lg-3 col-6'>
                    <div class='small-box bg-info'>
                        <div class='inner'>
                            <h3><?php echo number_format($stats['total_visitas']); ?></h3>
                            <p>Total de Visitas</p>
                        </div>
                        <div class='icon'>
                            <i class='fas fa-eye'></i>
                        </div>
                        <a href='#' class='small-box-footer'>Mais info <i class='fas fa-arrow-circle-right'></i></a>
                    </div>
                </div>

                <div class='col-lg-3 col-6'>
                    <div class='small-box bg-success'>
                        <div class='inner'>
                            <h3><?php echo number_format($stats['visitas_hoje']); ?></h3>
                            <p>Visitas Hoje</p>
                        </div>
                        <div class='icon'>
                            <i class='fas fa-calendar-day'></i>
                        </div>
                        <a href='#' class='small-box-footer'>Mais info <i class='fas fa-arrow-circle-right'></i></a>
                    </div>
                </div>

                <div class='col-lg-3 col-6'>
                    <div class='small-box bg-warning'>
                        <div class='inner'>
                            <h3><?php echo number_format($stats['visitas_semana']); ?></h3>
                            <p>Esta Semana</p>
                        </div>
                        <div class='icon'>
                            <i class='fas fa-calendar-week'></i>
                        </div>
                        <a href='#' class='small-box-footer'>Mais info <i class='fas fa-arrow-circle-right'></i></a>
                    </div>
                </div>

                <div class='col-lg-3 col-6'>
                    <div class='small-box bg-danger'>
                        <div class='inner'>
                            <h3><?php echo number_format($stats['visitas_mes']); ?></h3>
                            <p>Este Mês</p>
                        </div>
                        <div class='icon'>
                            <i class='fas fa-calendar-alt'></i>
                        </div>
                        <a href='#' class='small-box-footer'>Mais info <i class='fas fa-arrow-circle-right'></i></a>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class='row'>
                <!-- Gráfico de Visitas por Mês -->
                <div class='col-md-8'>
                    <div class='card card-primary'>
                        <div class='card-header'>
                            <h3 class='card-title'>
                                <i class='fas fa-chart-line mr-1'></i>
                                Visitas por Mês (Últimos 12 meses)
                            </h3>
                        </div>
                        <div class='card-body'>
                            <canvas id='visitasChart' style='height: 300px;'></canvas>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas Adicionais -->
                <div class='col-md-4'>
                    <div class='card card-info'>
                        <div class='card-header'>
                            <h3 class='card-title'>
                                <i class='fas fa-chart-bar mr-1'></i>
                                Estatísticas
                            </h3>
                        </div>
                        <div class='card-body'>
                            <div class='row'>
                                <div class='col-12'>
                                    <div class='info-box bg-light'>
                                        <span class='info-box-icon'><i class='fas fa-calendar'></i></span>
                                        <div class='info-box-content'>
                                            <span class='info-box-text'>Este Ano</span>
                                            <span class='info-box-number'><?php echo number_format($stats['visitas_ano']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class='col-12'>
                                    <div class='info-box bg-light'>
                                        <span class='info-box-icon'><i class='fas fa-chart-line'></i></span>
                                        <div class='info-box-content'>
                                            <span class='info-box-text'>Média Diária</span>
                                            <span class='info-box-number'><?php echo $stats['media_diaria']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Páginas -->
            <div class='row'>
                <div class='col-md-12'>
                    <div class='card card-success'>
                        <div class='card-header'>
                            <h3 class='card-title'>
                                <i class='fas fa-trophy mr-1'></i>
                                Páginas Mais Visitadas
                            </h3>
                        </div>
                        <div class='card-body table-responsive p-0'>
                            <table class='table table-hover text-nowrap'>
                                <thead>
                                    <tr>
                                        <th>Página</th>
                                        <th>Visitas</th>
                                        <th>Percentual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_visitas = $stats['total_visitas'];
                                    foreach ($stats['top_paginas'] as $pagina): 
                                        $percentual = $total_visitas > 0 ? round(($pagina['visitas'] / $total_visitas) * 100, 1) : 0;
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($pagina['pagina']); ?></td>
                                            <td><?php echo number_format($pagina['visitas']); ?></td>
                                            <td>
                                                <div class='progress progress-xs'>
                                                    <div class='progress-bar bg-success' style='width: <?php echo $percentual; ?>%'></div>
                                                </div>
                                                <span class='badge bg-success'><?php echo $percentual; ?>%</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Scripts (iframe) -->
<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>

<script>
$(document).ready(function() {
    // Dados para o gráfico
    var visitasData = <?php echo json_encode($stats['visitas_por_mes']); ?>;
    
    var labels = visitasData.map(function(item) {
        var date = new Date(item.mes + '-01');
        return date.toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' });
    });
    
    var data = visitasData.map(function(item) {
        return item.visitas;
    });

    // Criar gráfico
    var ctx = document.getElementById('visitasChart').getContext('2d');
    var visitasChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Visitas',
                data: data,
                borderColor: 'rgba(0, 123, 255, 1)',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('pt-BR');
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Visitas: ' + context.parsed.y.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });
});

// Ajustar altura do iframe quando carregado
window.addEventListener('load', function() {
    setTimeout(function() {
        const height = document.body.scrollHeight;
        if (window.parent) {
            window.parent.postMessage({
                type: 'resize-iframe',
                height: height + 50
            }, '*');
        }
    }, 100);
});
</script>

</body>
</html>
<?php } ?>

<?php
$conexao->close();
?>
