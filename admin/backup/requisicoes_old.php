<?php
// Incluir o arquivo de conexão e as funções de manipulação de requisições
include_once('../php/conexao.php');
include_once('../php/funcoes_requisicoes.php');

// Verificar se o formulário de filtro foi submetido
if(isset($_POST['filtrar'])) {
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];

    // Consultar requisições filtradas por data
    require_once('../php/funcoes_requisicoes.php');
    $requisicoes = listarRequisicoesPorData($conexao, $data_inicio, $data_fim);
} else {
    // Consultar todas as requisições do banco de dados
    require_once('../php/funcoes_requisicoes.php');
    $requisicoes = listarRequisicoes($conexao);
}

// Verificar se o formulário foi enviado e se há requisições para exportar
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['exportar']) && !empty($requisicoes)) {
    // Inicializar o objeto SimpleXMLElement para criar o XML
    $xml = new SimpleXMLElement('<requisicoes></requisicoes>');

    // Iterar sobre as requisições para adicionar cada uma ao XML
    foreach ($requisicoes as $requisicao) {
        // Criar um novo elemento "requisicao"
        $requisicaoXML = $xml->addChild('requisicao');

        // Adicionar os dados da requisição como elementos filho do elemento "requisicao"
        $requisicaoXML->addChild('id', $requisicao['id_requisicao']);
        $requisicaoXML->addChild('nome', $requisicao['nome']);
        $requisicaoXML->addChild('data_nascimento', isset($requisicao['data_nascimento']) ? $requisicao['data_nascimento'] : '');
        $requisicaoXML->addChild('email', $requisicao['email']);
        $requisicaoXML->addChild('telefone', $requisicao['telefone']);
        $requisicaoXML->addChild('horario_contato', $requisicao['horario_contato']);
        $requisicaoXML->addChild('tipo', $requisicao['tipo']);
        $requisicaoXML->addChild('categoria', $requisicao['categoria']);
        $requisicaoXML->addChild('outros_info', $requisicao['outros_info']);
        $requisicaoXML->addChild('data_requisicao', $requisicao['data_requisicao']);
    }

    // Definir cabeçalhos para forçar o download do XML
    header('Content-Disposition: attachment; filename="requisicoes.xml"');
    header('Content-Type: text/xml');

    // Imprimir o XML
    echo $xml->asXML();
    exit; // Parar a execução do script após gerar o XML
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CONFINTER - Gerenciamento de Requisições</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="assets/css/adminlte.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">

    <style>
        .status-badge {
            font-size: 0.8em;
        }
        .filter-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
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
                        <i class="fas fa-clipboard-list mr-2"></i>
                        Gerenciamento de Requisições
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
                        <li class="breadcrumb-item active">Requisições</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Filter Section -->
            <div class="filter-section">
                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label for="data_inicio" class="form-label">Data Início</label>
                        <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo isset($_POST['data_inicio']) ? $_POST['data_inicio'] : ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="data_fim" class="form-label">Data Fim</label>
                        <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo isset($_POST['data_fim']) ? $_POST['data_fim'] : ''; ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" name="filtrar" class="btn btn-primary me-2">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <button type="submit" name="exportar" class="btn btn-success" <?php echo empty($requisicoes) ? 'disabled' : ''; ?>>
                            <i class="fas fa-download"></i> Exportar XML
                        </button>
                    </div>
                </form>
            </div>

            <!-- Requests Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-1"></i>
                        Lista de Requisições
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="requisicoesTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Tipo</th>
                                <th>Categoria</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($requisicoes)) {
                                foreach ($requisicoes as $requisicao) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($requisicao['id_requisicao']) . "</td>";
                                    echo "<td>" . htmlspecialchars($requisicao['nome']) . "</td>";
                                    echo "<td>" . htmlspecialchars($requisicao['email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($requisicao['telefone']) . "</td>";
                                    echo "<td><span class='badge bg-primary'>" . htmlspecialchars($requisicao['tipo']) . "</span></td>";
                                    echo "<td><span class='badge bg-info'>" . htmlspecialchars($requisicao['categoria']) . "</span></td>";
                                    echo "<td>" . date('d/m/Y H:i', strtotime($requisicao['data_requisicao'])) . "</td>";
                                    echo "<td>";
                                    echo "<button class='btn btn-sm btn-info me-1' onclick='viewRequest(" . $requisicao['id_requisicao'] . ")' title='Visualizar'><i class='fas fa-eye'></i></button>";
                                    echo "<button class='btn btn-sm btn-warning me-1' onclick='editRequest(" . $requisicao['id_requisicao'] . ")' title='Editar'><i class='fas fa-edit'></i></button>";
                                    echo "<button class='btn btn-sm btn-danger' onclick='deleteRequest(" . $requisicao['id_requisicao'] . ")' title='Excluir'><i class='fas fa-trash'></i></button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center'>Nenhuma requisição encontrada.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
