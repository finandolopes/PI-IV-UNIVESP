<?php
session_start();
include_once('../php/conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Detectar se está em iframe
$is_iframe = isset($_GET['iframe']) || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin.php') !== false);

// Consulta SQL para buscar os clientes (corrigido - sem JOIN desnecessário)
$sql = "SELECT id_cliente, nome, email, telefone, cnpj, segmento, data_cadastro FROM clientes ORDER BY data_cadastro DESC";
$result = $conexao->query($sql);

// Exportar XML
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['exportar']) && $result->num_rows > 0) {
    $xml = new SimpleXMLElement('<clientes></clientes>');
    $result->data_seek(0); // Reset do ponteiro
    while($row = $result->fetch_assoc()) {
        $clienteXML = $xml->addChild('cliente');
        $clienteXML->addChild('id', $row['id_cliente']);
        $clienteXML->addChild('nome', htmlspecialchars($row['nome']));
        $clienteXML->addChild('email', $row['email']);
        $clienteXML->addChild('telefone', $row['telefone']);
        $clienteXML->addChild('cnpj', $row['cnpj']);
        $clienteXML->addChild('segmento', htmlspecialchars($row['segmento']));
    }
    header('Content-Disposition: attachment; filename="clientes.xml"');
    header('Content-Type: text/xml');
    echo $xml->asXML();
    exit;
}
?>

<?php if (!$is_iframe): ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clientes - CONFINTER</title>

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
                        <i class="fas fa-users mr-2"></i>
                        Lista de Clientes
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
                        <li class="breadcrumb-item">Clientes</li>
                        <li class="breadcrumb-item active">Lista</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-1"></i>
                                Todos os Clientes
                            </h3>
                            <div class="card-tools">
                                <form method="post" action="" style="display: inline;">
                                    <button type="submit" name="exportar" class="btn btn-success btn-sm">
                                        <i class="fas fa-file-export"></i> Exportar XML
                                    </button>
                                </form>
                                <button onclick="window.print()" class="btn btn-info btn-sm">
                                    <i class="fas fa-print"></i> Imprimir
                                </button>
                                <a href="novocliente.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Novo Cliente
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="clientes-table" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Telefone</th>
                                        <th>CNPJ</th>
                                        <th>Segmento</th>
                                        <th>Data Cadastro</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['id_cliente']; ?></td>
                                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><?php echo htmlspecialchars($row['telefone']); ?></td>
                                                <td><?php echo htmlspecialchars($row['cnpj']); ?></td>
                                                <td><?php echo htmlspecialchars($row['segmento']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($row['data_cadastro'])); ?></td>
                                                <td>
                                                    <a href="clientedit.php?id=<?php echo $row['id_cliente']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-danger" onclick="confirmarExclusao(<?php echo $row['id_cliente']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Nenhum cliente encontrado</td>
                                        </tr>
                                    <?php endif; ?>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    $('#clientes-table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json"
        },
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[0, "desc"]]
    });
});

function confirmarExclusao(id) {
    if (confirm('Tem certeza que deseja excluir este cliente?')) {
        window.location.href = '../php/excluir_cliente.php?id=' + id;
    }
}
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
                        <i class="fas fa-users mr-2"></i>
                        Lista de Clientes
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#" onclick="window.top.loadPage('admin.php')">Home</a></li>
                        <li class="breadcrumb-item">Clientes</li>
                        <li class="breadcrumb-item active">Lista</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-1"></i>
                                Todos os Clientes
                            </h3>
                            <div class="card-tools">
                                <form method="post" action="" style="display: inline;">
                                    <button type="submit" name="exportar" class="btn btn-success btn-sm">
                                        <i class="fas fa-file-export"></i> Exportar XML
                                    </button>
                                </form>
                                <button onclick="window.print()" class="btn btn-info btn-sm">
                                    <i class="fas fa-print"></i> Imprimir
                                </button>
                                <a href="#" onclick="window.top.loadPage('novocliente.php')" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Novo Cliente
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="clientes-table" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Telefone</th>
                                        <th>CNPJ</th>
                                        <th>Segmento</th>
                                        <th>Data Cadastro</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['id_cliente']; ?></td>
                                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><?php echo htmlspecialchars($row['telefone']); ?></td>
                                                <td><?php echo htmlspecialchars($row['cnpj']); ?></td>
                                                <td><?php echo htmlspecialchars($row['segmento']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($row['data_cadastro'])); ?></td>
                                                <td>
                                                    <a href="#" onclick="window.top.loadPage('clientedit.php?id=<?php echo $row['id_cliente']; ?>')" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-danger" onclick="confirmarExclusao(<?php echo $row['id_cliente']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Nenhum cliente encontrado</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#clientes-table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json"
        },
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[0, "desc"]]
    });
});

function confirmarExclusao(id) {
    if (confirm('Tem certeza que deseja excluir este cliente?')) {
        window.location.href = '../php/excluir_cliente.php?id=' + id;
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
