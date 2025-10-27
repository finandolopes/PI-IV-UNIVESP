<?php
session_start();
require_once '../php/conexao.php';

// Detectar se está em iframe
$is_iframe = isset($_GET['iframe']) || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin.php') !== false);

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar se é admin
if ($_SESSION['perfil'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Buscar logs do sistema
$query_logs = "SELECT * FROM logs ORDER BY data_hora DESC LIMIT 100";
$result_logs = $conn->query($query_logs);

// Buscar estatísticas dos logs
$query_stats = "SELECT
    COUNT(*) as total_logs,
    COUNT(CASE WHEN tipo = 'login' THEN 1 END) as total_logins,
    COUNT(CASE WHEN tipo = 'erro' THEN 1 END) as total_erros,
    COUNT(CASE WHEN tipo = 'acao' THEN 1 END) as total_acoes
FROM logs";
$result_stats = $conn->query($query_stats);
$stats = $result_stats->fetch_assoc();

$page_title = "Logs do Sistema";
?>

<?php if (!$is_iframe): ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?> - CONFINTER</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="assets/css/custom-admin.css">

    <style>
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
                        <i class="fas fa-history mr-2"></i>
                        Logs do Sistema
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admin.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Logs do Sistema</li>
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
                            <h3><?php echo $stats['total_logs']; ?></h3>
                            <p>Total de Logs</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <a href="#logs-table" class="small-box-footer">Ver Detalhes <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success stats-card">
                        <div class="inner">
                            <h3><?php echo $stats['total_logins']; ?></h3>
                            <p>Logins</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <a href="#logs-table" class="small-box-footer">Ver Detalhes <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning stats-card">
                        <div class="inner">
                            <h3><?php echo $stats['total_acoes']; ?></h3>
                            <p>Ações</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <a href="#logs-table" class="small-box-footer">Ver Detalhes <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger stats-card">
                        <div class="inner">
                            <h3><?php echo $stats['total_erros']; ?></h3>
                            <p>Erros</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <a href="#logs-table" class="small-box-footer">Ver Detalhes <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-1"></i>
                        Registros de Log
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="logs-table" class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Usuário</th>
                                <th>Tipo</th>
                                <th>Ação</th>
                                <th>IP</th>
                                <th>Detalhes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_logs && $result_logs->num_rows > 0): ?>
                                <?php while($log = $result_logs->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($log['data_hora'])); ?></td>
                                        <td><?php echo htmlspecialchars($log['usuario'] ?? 'Sistema'); ?></td>
                                        <td>
                                            <?php
                                            $tipo_class = 'badge-secondary';
                                            switch($log['tipo']) {
                                                case 'login': $tipo_class = 'badge-success'; break;
                                                case 'erro': $tipo_class = 'badge-danger'; break;
                                                case 'acao': $tipo_class = 'badge-warning'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $tipo_class; ?>"><?php echo ucfirst($log['tipo']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['acao']); ?></td>
                                        <td><?php echo htmlspecialchars($log['ip'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($log['detalhes'] ?? ''); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Nenhum log encontrado</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include 'footer.php'; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    $('#logs-table').DataTable({
        'language': {
            'lengthMenu': 'Mostrar _MENU_ registros por página',
            'zeroRecords': 'Nenhum registro encontrado',
            'info': 'Mostrando página _PAGE_ de _PAGES_',
            'infoEmpty': 'Nenhum registro disponível',
            'infoFiltered': '(filtrado de _MAX_ registros totais)',
            'search': 'Buscar:',
            'paginate': {
                'first': 'Primeiro',
                'last': 'Último',
                'next': 'Próximo',
                'previous': 'Anterior'
            }
        },
        'pageLength': 25,
        'responsive': true,
        'order': [[0, 'desc']]
    });
});

// Ajustar altura do iframe quando carregado
<?php if ($is_iframe): ?>
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
<?php endif; ?>
</script>

</body>
</html>
<?php else: ?>
<!-- Versão Iframe -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-history mr-2"></i>
                        Logs do Sistema
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#" onclick="window.top.loadPage('admin.php')">Dashboard</a></li>
                        <li class="breadcrumb-item active">Logs do Sistema</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info stats-card">
                        <div class="inner">
                            <h3><?php echo $stats['total_logs']; ?></h3>
                            <p>Total de Logs</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <a href="#logs-table" class="small-box-footer">Ver Detalhes <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success stats-card">
                        <div class="inner">
                            <h3><?php echo $stats['total_logins']; ?></h3>
                            <p>Logins</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <a href="#logs-table" class="small-box-footer">Ver Detalhes <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning stats-card">
                        <div class="inner">
                            <h3><?php echo $stats['total_acoes']; ?></h3>
                            <p>Ações</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <a href="#logs-table" class="small-box-footer">Ver Detalhes <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger stats-card">
                        <div class="inner">
                            <h3><?php echo $stats['total_erros']; ?></h3>
                            <p>Erros</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <a href="#logs-table" class="small-box-footer">Ver Detalhes <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-1"></i>
                        Registros de Log
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="logs-table" class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Usuário</th>
                                <th>Tipo</th>
                                <th>Ação</th>
                                <th>IP</th>
                                <th>Detalhes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_logs && $result_logs->num_rows > 0): ?>
                                <?php while($log = $result_logs->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($log['data_hora'])); ?></td>
                                        <td><?php echo htmlspecialchars($log['usuario'] ?? 'Sistema'); ?></td>
                                        <td>
                                            <?php
                                            $tipo_class = 'badge-secondary';
                                            switch($log['tipo']) {
                                                case 'login': $tipo_class = 'badge-success'; break;
                                                case 'erro': $tipo_class = 'badge-danger'; break;
                                                case 'acao': $tipo_class = 'badge-warning'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $tipo_class; ?>"><?php echo ucfirst($log['tipo']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['acao']); ?></td>
                                        <td><?php echo htmlspecialchars($log['ip'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($log['detalhes'] ?? ''); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Nenhum log encontrado</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#logs-table').DataTable({
        'language': {
            'lengthMenu': 'Mostrar _MENU_ registros por página',
            'zeroRecords': 'Nenhum registro encontrado',
            'info': 'Mostrando página _PAGE_ de _PAGES_',
            'infoEmpty': 'Nenhum registro disponível',
            'infoFiltered': '(filtrado de _MAX_ registros totais)',
            'search': 'Buscar:',
            'paginate': {
                'first': 'Primeiro',
                'last': 'Último',
                'next': 'Próximo',
                'previous': 'Anterior'
            }
        },
        'pageLength': 25,
        'responsive': true,
        'order': [[0, 'desc']]
    });
});

// Ajustar altura do iframe quando carregado
<?php if ($is_iframe): ?>
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
<?php endif; ?>
</script>
<?php endif; ?>

<?php
$conn->close();
?>