<?php
session_start();
include_once(__DIR__ . '/../php/conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Detectar se está em iframe
$is_iframe = isset($_GET['iframe']) || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin.php') !== false);

// Consultas para relatórios
// Visitas por dia (últimos 30 dias)
$query_visitas_dia = "SELECT DATE(data_visita) as dia, COUNT(*) as total FROM contador_visitas WHERE data_visita >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(data_visita) ORDER BY dia";
$result_visitas_dia = mysqli_query($conexao, $query_visitas_dia);

// Requisições por categoria
$query_req_categoria = "SELECT categoria, COUNT(*) as total FROM requisicoes GROUP BY categoria ORDER BY total DESC";
$result_req_categoria = mysqli_query($conexao, $query_req_categoria);

// Tempo médio de acesso
$query_tempo_medio = "SELECT AVG(tempo) as tempo_medio FROM contador_visitas WHERE tempo > 0";
$result_tempo_medio = mysqli_query($conexao, $query_tempo_medio);
$row_tempo = mysqli_fetch_assoc($result_tempo_medio);
$tempo_medio_segundos = $row_tempo['tempo_medio'] ?? 0;
$tempo_medio = $tempo_medio_segundos > 0 ? gmdate("i:s", round($tempo_medio_segundos)) : '00:00';

// Total de usuários ativos
$query_usuarios_ativos = "SELECT COUNT(*) as total FROM adm WHERE perfil = 'admin'";
$result_usuarios_ativos = mysqli_query($conexao, $query_usuarios_ativos);
$row_usuarios = mysqli_fetch_assoc($result_usuarios_ativos);
$total_usuarios_ativos = $row_usuarios['total'];

// Páginas com mais tempo de acesso
$query_paginas_tempo = "SELECT pagina, AVG(tempo) as tempo_medio, COUNT(*) as acessos FROM contador_visitas WHERE tempo > 0 GROUP BY pagina ORDER BY tempo_medio DESC LIMIT 10";
$result_paginas_tempo = mysqli_query($conexao, $query_paginas_tempo);

// Preparar dados para gráficos
$visitas_data = [];
while ($row = mysqli_fetch_assoc($result_visitas_dia)) {
    $visitas_data[] = $row['total'];
}
$visitas_labels = [];
$result_visitas_dia = mysqli_query($conexao, $query_visitas_dia); // Reset result pointer
while ($row = mysqli_fetch_assoc($result_visitas_dia)) {
    $visitas_labels[] = "'" . date('d/m', strtotime($row['dia'])) . "'";
}

$req_labels = [];
$req_data = [];
while ($row = mysqli_fetch_assoc($result_req_categoria)) {
    $req_labels[] = "'" . $row['categoria'] . "'";
    $req_data[] = $row['total'];
}

if (!$is_iframe) {
    // Versão completa com navbar e sidebar
    include 'navbar.php';
    include 'sidebar.php';
?>
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <!-- Export Buttons -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="exportToExcel()">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="exportToPDF()">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="exportToCSV()">
                            <i class="fas fa-file-csv"></i> CSV
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $tempo_medio; ?></h3>
                            <p>Tempo Médio</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo number_format($total_usuarios_ativos); ?></h3>
                            <p>Usuários Ativos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo mysqli_num_rows($result_req_categoria); ?></h3>
                            <p>Categorias</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo mysqli_num_rows($result_paginas_tempo); ?></h3>
                            <p>Páginas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-1"></i>
                                Visitas nos Últimos 30 Dias
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="visitsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Requisições por Categoria
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="requestsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables Row -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock mr-1"></i>
                                Páginas com Maior Tempo de Acesso
                            </h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Página</th>
                                        <th>Tempo Médio</th>
                                        <th>Acessos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    mysqli_data_seek($result_paginas_tempo, 0); // Reset result pointer
                                    while ($row = mysqli_fetch_assoc($result_paginas_tempo)) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['pagina']) . "</td>";
                                        echo "<td>" . gmdate("i:s", $row['tempo_medio']) . "</td>";
                                        echo "<td><span class='badge bg-primary'>" . $row['acessos'] . "</span></td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-tags mr-1"></i>
                                Requisições por Categoria
                            </h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Categoria</th>
                                        <th>Total</th>
                                        <th>Percentual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total_req = array_sum($req_data);
                                    mysqli_data_seek($result_req_categoria, 0); // Reset result pointer
                                    while ($row = mysqli_fetch_assoc($result_req_categoria)) {
                                        $percentual = $total_req > 0 ? round(($row['total'] / $total_req) * 100, 1) : 0;
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['categoria']) . "</td>";
                                        echo "<td>" . $row['total'] . "</td>";
                                        echo "<td><div class='progress progress-xs'><div class='progress-bar bg-info' style='width: " . $percentual . "%'></div></div><span class='badge bg-info'>" . $percentual . "%</span></td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include 'footer.php'; ?>

<?php } else { ?>
<!-- Versão Iframe -->
<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Relatórios e Estatísticas - CONFINTER</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f4f6f9; margin: 0; padding: 20px; }
        .content-wrapper { margin: 0; background: transparent; }
        .card { box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
        .chart-container { position: relative; height: 400px; width: 100%; }

        /* Estilos específicos para relatórios */
        .relatorio-card {
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .relatorio-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .card-text {
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 0.5rem;
        }

        .btn-group .btn {
            flex: 1;
            margin: 0 1px;
        }

        .btn-group .btn:first-child {
            margin-left: 0;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }

        /* Melhorar responsividade */
        @media (max-width: 768px) {
            .relatorio-card .card-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.5rem;
            }

            .relatorio-card .card-header .badge {
                align-self: flex-end;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn-group .btn {
                margin: 1px 0;
            }
        }

        /* Loading states */
        .btn.loading {
            opacity: 0.7;
            pointer-events: none;
            position: relative;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            width: 1rem;
            height: 1rem;
            top: 50%;
            left: 50%;
            margin-left: -0.5rem;
            margin-top: -0.5rem;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Alertas customizados */
        .alert-fixed {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $tempo_medio; ?></h3>
                            <p>Tempo Médio</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo number_format($total_usuarios_ativos); ?></h3>
                            <p>Usuários Ativos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo mysqli_num_rows($result_req_categoria); ?></h3>
                            <p>Categorias</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo mysqli_num_rows($result_paginas_tempo); ?></h3>
                            <p>Páginas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-1"></i>
                                Visitas nos Últimos 30 Dias
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="visitsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Requisições por Categoria
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="requestsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables Row -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock mr-1"></i>
                                Páginas com Maior Tempo de Acesso
                            </h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Página</th>
                                        <th>Tempo Médio</th>
                                        <th>Acessos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    mysqli_data_seek($result_paginas_tempo, 0); // Reset result pointer
                                    while ($row = mysqli_fetch_assoc($result_paginas_tempo)) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['pagina']) . "</td>";
                                        echo "<td>" . gmdate("i:s", $row['tempo_medio']) . "</td>";
                                        echo "<td><span class='badge bg-primary'>" . $row['acessos'] . "</span></td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-tags mr-1"></i>
                                Requisições por Categoria
                            </h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Categoria</th>
                                        <th>Total</th>
                                        <th>Percentual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total_req = array_sum($req_data);
                                    mysqli_data_seek($result_req_categoria, 0); // Reset result pointer
                                    while ($row = mysqli_fetch_assoc($result_req_categoria)) {
                                        $percentual = $total_req > 0 ? round(($row['total'] / $total_req) * 100, 1) : 0;
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['categoria']) . "</td>";
                                        echo "<td>" . $row['total'] . "</td>";
                                        echo "<td><div class='progress progress-xs'><div class='progress-bar bg-info' style='width: " . $percentual . "%'></div></div><span class='badge bg-info'>" . $percentual . "%</span></td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php } ?>

<script>
$(document).ready(function() {
    // Visits Chart
    var visitsCtx = document.getElementById('visitsChart').getContext('2d');
    var visitsChart = new Chart(visitsCtx, {
        type: 'line',
        data: {
            labels: [<?php echo implode(', ', $visitas_labels); ?>],
            datasets: [{
                label: 'Visitas',
                data: [<?php echo implode(', ', $visitas_data); ?>],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
        }
    });

    // Requests Chart
    var reqCtx = document.getElementById('requestsChart').getContext('2d');
    var reqChart = new Chart(reqCtx, {
        type: 'doughnut',
        data: {
            labels: [<?php echo implode(', ', $req_labels); ?>],
            datasets: [{
                data: [<?php echo implode(', ', $req_data); ?>],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)',
                    'rgb(255, 159, 64)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
});

// Export functions
function exportToExcel() {
    var table = document.createElement('table');
    var thead = document.createElement('thead');
    var tbody = document.createElement('tbody');

    var headerRow = document.createElement('tr');
    var headers = ['Métrica', 'Valor'];
    headers.forEach(function(header) {
        var th = document.createElement('th');
        th.textContent = header;
        headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);

    var data = [
        ['Tempo Médio de Acesso', '<?php echo $tempo_medio; ?>'],
        ['Usuários Ativos', '<?php echo number_format($total_usuarios_ativos); ?>'],
        ['Total de Visitas (30 dias)', '<?php echo array_sum($visitas_data); ?>'],
        ['Total de Requisições', '<?php echo $total_req; ?>']
    ];

    data.forEach(function(rowData) {
        var row = document.createElement('tr');
        rowData.forEach(function(cellData) {
            var td = document.createElement('td');
            td.textContent = cellData;
            row.appendChild(td);
        });
        tbody.appendChild(row);
    });

    table.appendChild(thead);
    table.appendChild(tbody);

    var html = table.outerHTML;
    var blob = new Blob([html], { type: 'application/vnd.ms-excel' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'relatorio_' + new Date().toISOString().split('T')[0] + '.xls';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function exportToPDF() {
    alert('Funcionalidade de export para PDF será implementada em breve.');
}

function exportToCSV() {
    var csvContent = 'Métrica,Valor\n';
    csvContent += 'Tempo Médio de Acesso,<?php echo $tempo_medio; ?>\n';
    csvContent += 'Usuários Ativos,<?php echo number_format($total_usuarios_ativos); ?>\n';
    csvContent += 'Total de Visitas (30 dias),<?php echo array_sum($visitas_data); ?>\n';
    csvContent += 'Total de Requisições,<?php echo $total_req; ?>\n';

    var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'relatorio_' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>

<?php
mysqli_close($conexao);
?>
