<?php
include_once('session_check.php');
include_once('../php/conexao.php');
include_once('../php/funcoes_auditoria.php');

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
$tempo_medio = $row_tempo['tempo_medio'] ? gmdate("i:s", $row_tempo['tempo_medio']) : '00:00';

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

log_auditoria($conexao, $_SESSION['usuario_id'], 'acesso_relatorios');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CONFINTER - Relatórios e Estatísticas</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="assets/css/adminlte.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
        .stats-card {
            transition: transform 0.2s ease-in-out;
        }
        .stats-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Relatórios e Estatísticas
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
                        <li class="breadcrumb-item active">Relatórios</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info stats-card">
                        <div class="inner">
                            <h3><?php echo $tempo_medio; ?></h3>
                            <p>Tempo Médio de Acesso</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="small-box-footer">&nbsp;</div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success stats-card">
                        <div class="inner">
                            <h3><?php echo number_format($total_usuarios_ativos); ?></h3>
                            <p>Usuários Ativos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="small-box-footer">&nbsp;</div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning stats-card">
                        <div class="inner">
                            <h3><?php echo mysqli_num_rows($result_req_categoria); ?></h3>
                            <p>Categorias de Requisições</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="small-box-footer">&nbsp;</div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger stats-card">
                        <div class="inner">
                            <h3><?php echo mysqli_num_rows($result_paginas_tempo); ?></h3>
                            <p>Páginas Analisadas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="small-box-footer">&nbsp;</div>
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
                                Visitas nos Últimos 30 Dias
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="visitsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
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
                    <div class="card">
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
                    <div class="card">
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

<!-- Chart.js Scripts -->
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
