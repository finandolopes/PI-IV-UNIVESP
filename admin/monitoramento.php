<?php
session_start();
include('../php/conexao.php');

// Detectar se está em iframe
$is_iframe = isset($_GET['iframe']) || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin.php') !== false);

// Verificar se o usuário está logado
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Consultas iniciais para métricas
$sqlVisitas = "SELECT COUNT(*) as total FROM contador_visitas WHERE DATE(data_visita) = CURDATE()";
$resultVisitas = mysqli_query($conexao, $sqlVisitas);
$visitasHoje = 0;
if ($resultVisitas) {
    $row = mysqli_fetch_assoc($resultVisitas);
    $visitasHoje = $row ? $row['total'] : 0;
}

$sqlRequisicoes = "SELECT COUNT(*) as total FROM requisicoes WHERE DATE(data_requisicao) = CURDATE()";
$resultRequisicoes = mysqli_query($conexao, $sqlRequisicoes);
$requisicoesHoje = 0;
if ($resultRequisicoes) {
    $row = mysqli_fetch_assoc($resultRequisicoes);
    $requisicoesHoje = $row ? $row['total'] : 0;
}

$sqlDepoimentos = "SELECT COUNT(*) as total FROM depoimentos WHERE aprovado = 0";
$resultDepoimentos = mysqli_query($conexao, $sqlDepoimentos);
$depoimentosPendentes = 0;
if ($resultDepoimentos) {
    $row = mysqli_fetch_assoc($resultDepoimentos);
    $depoimentosPendentes = $row ? $row['total'] : 0;
}

$sqlTempoMedio = "SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(tempo))) as media FROM contador_visitas";
$resultTempoMedio = mysqli_query($conexao, $sqlTempoMedio);
$tempoMedio = null;
if ($resultTempoMedio) {
    $row = mysqli_fetch_assoc($resultTempoMedio);
    $tempoMedio = $row ? $row['media'] : null;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CONFINTER - Monitoramento em Tempo Real</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="assets/css/adminlte.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .real-time-card {
            transition: all 0.3s ease;
        }
        .real-time-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<?php if (!$is_iframe): ?>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<?php else: ?>
<body>
<div class="container-fluid">
<?php endif; ?>
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Monitoramento em Tempo Real
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
                        <li class="breadcrumb-item active">Monitoramento</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Real-time metrics -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info real-time-card pulse">
                        <div class="inner">
                            <h3 id="visitasHoje"><?php echo number_format($visitasHoje); ?></h3>
                            <p>Visitas Hoje</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <a href="#visitasTable" class="small-box-footer">
                            Ver Detalhes <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success real-time-card pulse">
                        <div class="inner">
                            <h3 id="requisicoesHoje"><?php echo number_format($requisicoesHoje); ?></h3>
                            <p>Requisições Hoje</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <a href="#requisicoesTable" class="small-box-footer">
                            Ver Detalhes <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning real-time-card pulse">
                        <div class="inner">
                            <h3 id="depoimentosPendentes"><?php echo number_format($depoimentosPendentes); ?></h3>
                            <p>Depoimentos Pendentes</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <a href="mod_depoimentos.php" class="small-box-footer">
                            Moderar <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger real-time-card pulse">
                        <div class="inner">
                            <h3 id="tempoMedio"><?php echo $tempoMedio ?: '00:00:00'; ?></h3>
                            <p>Tempo Médio no Site</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-1"></i>
                                Atividade em Tempo Real
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="realTimeChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-globe mr-1"></i>
                                Localização das Visitas
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="locationChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables Row -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card" id="visitasTable">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-eye mr-1"></i>
                                Visitas Recentes
                            </h3>
                        </div>
                        <div class="card-body">
                            <table id="visitasTableData" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>IP</th>
                                        <th>Data/Hora</th>
                                        <th>Tempo</th>
                                        <th>Página</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sqlVisitasRecentes = "SELECT ip_address, data_visita, tempo, pagina FROM contador_visitas ORDER BY data_visita DESC LIMIT 10";
                                    $resultVisitasRecentes = mysqli_query($conexao, $sqlVisitasRecentes);
                                    while ($row = mysqli_fetch_assoc($resultVisitasRecentes)) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['ip_address']) . "</td>";
                                        echo "<td>" . date('d/m/Y H:i', strtotime($row['data_visita'])) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['tempo']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['pagina']) . "</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card" id="requisicoesTable">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clipboard-list mr-1"></i>
                                Requisições Recentes
                            </h3>
                        </div>
                        <div class="card-body">
                            <table id="requisicoesTableData" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Data</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sqlRequisicoesRecentes = "SELECT nome, email, data_requisicao, status FROM requisicoes ORDER BY data_requisicao DESC LIMIT 10";
                                    $resultRequisicoesRecentes = mysqli_query($conexao, $sqlRequisicoesRecentes);
                                    while ($row = mysqli_fetch_assoc($resultRequisicoesRecentes)) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                        echo "<td>" . date('d/m/Y H:i', strtotime($row['data_requisicao'])) . "</td>";
                                        echo "<td><span class='badge bg-primary'>" . htmlspecialchars($row['status']) . "</span></td>";
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

<!-- Chart.js and DataTables Scripts -->
<script>
$(document).ready(function() {
    // Initialize DataTables
    $('#visitasTableData').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json"
        }
    });

    $('#requisicoesTableData').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json"
        }
    });

    // Real-time Chart
    var ctx = document.getElementById('realTimeChart').getContext('2d');
    var realTimeChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Visitas',
                data: [],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Location Chart
    var locationCtx = document.getElementById('locationChart').getContext('2d');
    var locationChart = new Chart(locationCtx, {
        type: 'doughnut',
        data: {
            labels: ['São Paulo', 'Rio de Janeiro', 'Minas Gerais', 'Outros'],
            datasets: [{
                data: [45, 25, 15, 15],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Update metrics every 30 seconds
    setInterval(function() {
        updateMetrics();
    }, 30000);

    function updateMetrics() {
        $.ajax({
            url: 'api/get_real_time_data.php',
            method: 'GET',
            success: function(data) {
                // Update counters
                $('#visitasHoje').text(data.visitasHoje);
                $('#requisicoesHoje').text(data.requisicoesHoje);
                $('#depoimentosPendentes').text(data.depoimentosPendentes);
                $('#tempoMedio').text(data.tempoMedio);

                // Update chart
                realTimeChart.data.labels.push(new Date().toLocaleTimeString());
                realTimeChart.data.datasets[0].data.push(data.visitasHoje);
                if (realTimeChart.data.labels.length > 20) {
                    realTimeChart.data.labels.shift();
                    realTimeChart.data.datasets[0].data.shift();
                }
                realTimeChart.update();
            }
        });
    }
});
</script>

