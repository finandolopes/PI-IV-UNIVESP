<?php
session_start();
include_once(__DIR__ . '/../php/conexao.php');

// Detectar se está em iframe
$is_iframe = isset($_GET['iframe']) || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin.php') !== false);

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Filtros e busca
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tipo_filter = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'todos';

// Query base
$query = "SELECT c.*, c.id_cliente as id, 0 as total_projetos FROM clientes c WHERE 1=1";
$params = [];
$types = '';

// Aplicar filtros
if (!empty($search)) {
    $query .= " AND (c.razao_social LIKE ? OR c.cnpj LIKE ? OR c.email LIKE ? OR c.responsavel LIKE ?)";
    $search_param = '%' . $search . '%';
    $params = array_fill(0, 4, $search_param);
    $types .= str_repeat('s', 4);
}

if ($tipo_filter !== 'todos') {
    $query .= " AND c.tipo = ?";
    $params[] = $tipo_filter;
    $types .= 's';
}

if ($status_filter !== 'todos') {
    $query .= " AND c.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$query .= " ORDER BY c.data_cadastro DESC";

// Executar query
if (!empty($params)) {
    $stmt = $conexao->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = mysqli_query($conexao, $query);
}

$clientes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $clientes[] = $row;
}

// Estatísticas
$stats = [
    'total' => count($clientes),
    'pj' => 0,
    'pf' => 0,
    'ativo' => 0,
    'inativo' => 0
];

foreach ($clientes as $cliente) {
    $stats[$cliente['tipo']]++;
    $stats[$cliente['status']]++;
}
?>

<?php if (!$is_iframe): ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Empresas - CONFINTER</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="assets/css/custom-admin.css">

    <style>
        /* Estilos específicos para clientes */
        .cliente-card {
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .cliente-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .cliente-card.status-ativo {
            border-left: 4px solid #28a745;
        }

        .cliente-card.status-inativo {
            border-left: 4px solid #6c757d;
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
            .cliente-card .card-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.5rem;
            }

            .cliente-card .card-header .badge {
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

        /* Filtro de status */
        #statusFilter, #tipoFilter {
            min-width: 150px;
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
                        <i class="fas fa-search mr-2"></i>
                        Buscar Empresas
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admin.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Buscar Empresas</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <p class="text-muted">Busque e gerencie empresas e clientes cadastrados no sistema</p>

            <!-- Estatísticas -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo number_format($stats['total'], 0, ',', '.'); ?></h3>
                            <p>Total de Clientes</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo number_format($stats['pj'], 0, ',', '.'); ?></h3>
                            <p>Pessoa Jurídica</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-industry"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo number_format($stats['pf'], 0, ',', '.'); ?></h3>
                            <p>Pessoa Física</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3><?php echo number_format($stats['ativo'], 0, ',', '.'); ?></h3>
                            <p>Clientes Ativos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter mr-1"></i>
                        Filtros de Busca
                    </h3>
                </div>
                <div class="card-body">
                    <form method="get" action="">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search">Buscar:</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                           value="<?php echo htmlspecialchars($search); ?>"
                                           placeholder="Razão social, CNPJ, email...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tipo">Tipo:</label>
                                    <select class="form-control" id="tipo" name="tipo">
                                        <option value="todos" <?php echo $tipo_filter === 'todos' ? 'selected' : ''; ?>>Todos</option>
                                        <option value="pj" <?php echo $tipo_filter === 'pj' ? 'selected' : ''; ?>>Pessoa Jurídica</option>
                                        <option value="pf" <?php echo $tipo_filter === 'pf' ? 'selected' : ''; ?>>Pessoa Física</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Status:</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="todos" <?php echo $status_filter === 'todos' ? 'selected' : ''; ?>>Todos</option>
                                        <option value="ativo" <?php echo $status_filter === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                        <option value="inativo" <?php echo $status_filter === 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabela de Resultados -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="fas fa-list"></i> Lista de Clientes</h5>
                    <div class="card-tools">
                        <select id="statusFilter" class="form-select form-select-sm mr-2">
                            <option value="">Todos os Status</option>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                        <select id="tipoFilter" class="form-select form-select-sm mr-2">
                            <option value="">Todos os Tipos</option>
                            <option value="pj">Pessoa Jurídica</option>
                            <option value="pf">Pessoa Física</option>
                        </select>
                        <a href="cadastrausuario.php<?php echo $is_iframe ? '?iframe=1' : ''; ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-plus mr-1"></i> Novo Cliente
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="clientesContainer">
                        <?php if (empty($clientes)): ?>
                        <div class="col-12">
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-search fa-3x mb-3"></i>
                                <p>Nenhum cliente encontrado com os filtros aplicados.</p>
                                <small>Tente ajustar os filtros de busca.</small>
                            </div>
                        </div>
                        <?php else: ?>
                            <?php foreach ($clientes as $cliente): ?>
                            <div class="col-xl-3 col-lg-4 col-md-6 mb-4 cliente-item" data-status="<?php echo $cliente['status']; ?>" data-tipo="<?php echo $cliente['tipo']; ?>">
                                <div class="card cliente-card status-<?php echo $cliente['status']; ?> h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-building"></i> <?php echo htmlspecialchars($cliente['razao_social'] ?? 'Cliente'); ?>
                                        </h6>
                                        <span class="badge bg-<?php echo $cliente['status'] === 'ativo' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($cliente['status'] ?? 'inativo'); ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <strong>CNPJ/CPF:</strong> <?php echo htmlspecialchars($cliente['cnpj'] ?? 'N/A'); ?>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Tipo:</strong>
                                            <span class="badge badge-<?php echo $cliente['tipo'] === 'pj' ? 'success' : 'warning'; ?> ml-1">
                                                <?php echo $cliente['tipo'] === 'pj' ? 'Pessoa Jurídica' : 'Pessoa Física'; ?>
                                            </span>
                                        </div>
                                        <?php if ($cliente['responsavel']): ?>
                                        <div class="mb-2">
                                            <strong>Responsável:</strong> <?php echo htmlspecialchars($cliente['responsavel']); ?>
                                        </div>
                                        <?php endif; ?>
                                        <div class="mb-2">
                                            <strong>Projetos:</strong>
                                            <span class="badge badge-info"><?php echo $cliente['total_projetos']; ?> projeto(s)</span>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($cliente['data_cadastro'])); ?>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="btn-group w-100" role="group">
                                            <button class="btn btn-info btn-sm" onclick="visualizarCliente(<?php echo $cliente['id']; ?>)">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                            <button class="btn btn-warning btn-sm" onclick="editarCliente(<?php echo $cliente['id']; ?>)">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="deletarCliente(<?php echo $cliente['id']; ?>, '<?php echo htmlspecialchars($cliente['razao_social'] ?? 'Cliente'); ?>')">
                                                <i class="fas fa-trash"></i> Excluir
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include 'footer.php'; ?>

<!-- Modal para visualizar cliente -->
<div class="modal fade" id="clienteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Visualizar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="clienteModalBody">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Filtro de status
    $('#statusFilter').change(function() {
        var status = $(this).val();
        if (status === '') {
            $('.cliente-item').show();
        } else {
            $('.cliente-item').hide();
            $('.cliente-item[data-status="' + status + '"]').show();
        }
    });

    // Filtro de tipo
    $('#tipoFilter').change(function() {
        var tipo = $(this).val();
        if (tipo === '') {
            $('.cliente-item').show();
        } else {
            $('.cliente-item').hide();
            $('.cliente-item[data-tipo="' + tipo + '"]').show();
        }
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

function visualizarCliente(id) {
    window.location.href = 'clientedit.php?id=' + id + '<?php echo $is_iframe ? '&iframe=1' : ''; ?>';
}

function editarCliente(id) {
    window.location.href = 'clientedit.php?id=' + id + '&action=edit<?php echo $is_iframe ? '&iframe=1' : ''; ?>';
}

function deletarCliente(id, nome) {
    if (confirm('Tem certeza que deseja deletar o cliente "' + nome + '"? Esta ação não pode ser desfeita.')) {
        // Implementar lógica de deleção
        alert('Funcionalidade de deleção em desenvolvimento. ID: ' + id);
    }
}

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
<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Buscar Empresas - CONFINTER</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <style>
        body { background: #f4f6f9; margin: 0; padding: 20px; }
        .content-wrapper { margin: 0; background: transparent; }
        .card { box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }

        /* Estilos específicos para clientes */
        .cliente-card {
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .cliente-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .cliente-card.status-ativo {
            border-left: 4px solid #28a745;
        }

        .cliente-card.status-inativo {
            border-left: 4px solid #6c757d;
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
            .cliente-card .card-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.5rem;
            }

            .cliente-card .card-header .badge {
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

        /* Filtro de status */
        #statusFilter, #tipoFilter {
            min-width: 150px;
        }
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
                        <i class="fas fa-search mr-2"></i>
                        Buscar Empresas
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#" onclick="window.top.loadPage('admin.php')">Dashboard</a></li>
                        <li class="breadcrumb-item active">Buscar Empresas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <p class="text-muted">Busque e gerencie empresas e clientes cadastrados no sistema</p>

            <!-- Estatísticas -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo number_format($stats['total'], 0, ',', '.'); ?></h3>
                            <p>Total de Clientes</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo number_format($stats['pj'], 0, ',', '.'); ?></h3>
                            <p>Pessoa Jurídica</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-industry"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo number_format($stats['pf'], 0, ',', '.'); ?></h3>
                            <p>Pessoa Física</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3><?php echo number_format($stats['ativo'], 0, ',', '.'); ?></h3>
                            <p>Clientes Ativos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter mr-1"></i>
                        Filtros de Busca
                    </h3>
                </div>
                <div class="card-body">
                    <form method="get" action="">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search">Buscar:</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                               value="<?php echo htmlspecialchars($search); ?>"
                                               placeholder="Razão social, CNPJ, email...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tipo">Tipo:</label>
                                    <select class="form-control" id="tipo" name="tipo">
                                        <option value="todos" <?php echo $tipo_filter === 'todos' ? 'selected' : ''; ?>>Todos</option>
                                        <option value="pj" <?php echo $tipo_filter === 'pj' ? 'selected' : ''; ?>>Pessoa Jurídica</option>
                                        <option value="pf" <?php echo $tipo_filter === 'pf' ? 'selected' : ''; ?>>Pessoa Física</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Status:</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="todos" <?php echo $status_filter === 'todos' ? 'selected' : ''; ?>>Todos</option>
                                        <option value="ativo" <?php echo $status_filter === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                        <option value="inativo" <?php echo $status_filter === 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabela de Resultados -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="fas fa-list"></i> Lista de Clientes</h5>
                    <div class="card-tools">
                        <select id="statusFilter" class="form-select form-select-sm mr-2">
                            <option value="">Todos os Status</option>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                        <select id="tipoFilter" class="form-select form-select-sm mr-2">
                            <option value="">Todos os Tipos</option>
                            <option value="pj">Pessoa Jurídica</option>
                            <option value="pf">Pessoa Física</option>
                        </select>
                        <a href="cadastrausuario.php<?php echo $is_iframe ? '?iframe=1' : ''; ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-plus mr-1"></i> Novo Cliente
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="clientesContainer">
                        <?php if (empty($clientes)): ?>
                        <div class="col-12">
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-search fa-3x mb-3"></i>
                                <p>Nenhum cliente encontrado com os filtros aplicados.</p>
                                <small>Tente ajustar os filtros de busca.</small>
                            </div>
                        </div>
                        <?php else: ?>
                            <?php foreach ($clientes as $cliente): ?>
                            <div class="col-xl-3 col-lg-4 col-md-6 mb-4 cliente-item" data-status="<?php echo $cliente['status']; ?>" data-tipo="<?php echo $cliente['tipo']; ?>">
                                <div class="card cliente-card status-<?php echo $cliente['status']; ?> h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-building"></i> <?php echo htmlspecialchars($cliente['razao_social'] ?? 'Cliente'); ?>
                                        </h6>
                                        <span class="badge bg-<?php echo $cliente['status'] === 'ativo' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($cliente['status'] ?? 'inativo'); ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <strong>CNPJ/CPF:</strong> <?php echo htmlspecialchars($cliente['cnpj'] ?? 'N/A'); ?>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Tipo:</strong>
                                            <span class="badge badge-<?php echo $cliente['tipo'] === 'pj' ? 'success' : 'warning'; ?> ml-1">
                                                <?php echo $cliente['tipo'] === 'pj' ? 'Pessoa Jurídica' : 'Pessoa Física'; ?>
                                            </span>
                                        </div>
                                        <?php if ($cliente['responsavel']): ?>
                                        <div class="mb-2">
                                            <strong>Responsável:</strong> <?php echo htmlspecialchars($cliente['responsavel']); ?>
                                        </div>
                                        <?php endif; ?>
                                        <div class="mb-2">
                                            <strong>Projetos:</strong>
                                            <span class="badge badge-info"><?php echo $cliente['total_projetos']; ?> projeto(s)</span>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($cliente['data_cadastro'])); ?>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="btn-group w-100" role="group">
                                            <button class="btn btn-info btn-sm" onclick="visualizarCliente(<?php echo $cliente['id']; ?>)">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                            <button class="btn btn-warning btn-sm" onclick="editarCliente(<?php echo $cliente['id']; ?>)">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="deletarCliente(<?php echo $cliente['id']; ?>, '<?php echo htmlspecialchars($cliente['razao_social'] ?? 'Cliente'); ?>')">
                                                <i class="fas fa-trash"></i> Excluir
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal para visualizar cliente -->
<div class="modal fade" id="clienteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Visualizar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="clienteModalBody">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Filtro de status
    $('#statusFilter').change(function() {
        var status = $(this).val();
        if (status === '') {
            $('.cliente-item').show();
        } else {
            $('.cliente-item').hide();
            $('.cliente-item[data-status="' + status + '"]').show();
        }
    });

    // Filtro de tipo
    $('#tipoFilter').change(function() {
        var tipo = $(this).val();
        if (tipo === '') {
            $('.cliente-item').show();
        } else {
            $('.cliente-item').hide();
            $('.cliente-item[data-tipo="' + tipo + '"]').show();
        }
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

function visualizarCliente(id) {
    window.location.href = 'clientedit.php?id=' + id + '<?php echo $is_iframe ? '&iframe=1' : ''; ?>';
}

function editarCliente(id) {
    window.location.href = 'clientedit.php?id=' + id + '&action=edit<?php echo $is_iframe ? '&iframe=1' : ''; ?>';
}

function deletarCliente(id, nome) {
    if (confirm('Tem certeza que deseja deletar o cliente "' + nome + '"? Esta ação não pode ser desfeita.')) {
        // Implementar lógica de deleção
        alert('Funcionalidade de deleção em desenvolvimento. ID: ' + id);
    }
}

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
$conexao->close();
?>
