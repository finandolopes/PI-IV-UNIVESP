<?php
session_start();
include_once('../php/conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Criar tabela slider_imagens se não existir
$createTableSQL = "CREATE TABLE IF NOT EXISTS `slider_imagens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) DEFAULT NULL,
  `descricao` text,
  `imagem` varchar(255) NOT NULL,
  `ordem` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `data_cadastro` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";

mysqli_query($conexao, $createTableSQL);

// Processar upload de imagem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'upload') {
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/img/slider/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_name = basename($_FILES['imagem']['name']);
            $file_path = $upload_dir . $file_name;

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($_FILES['imagem']['type'], $allowed_types)) {
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $file_path)) {
                    // Salvar no banco de dados
                    $titulo = $_POST['titulo'] ?? '';
                    $descricao = $_POST['descricao'] ?? '';
                    $ordem = $_POST['ordem'] ?? 0;
                    $ativo = isset($_POST['ativo']) ? 1 : 0;

                    $stmt = $conexao->prepare("INSERT INTO slider_imagens (titulo, descricao, imagem, ordem, ativo, data_cadastro) VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("sssii", $titulo, $descricao, $file_name, $ordem, $ativo);
                    $stmt->execute();
                    $stmt->close();

                    $_SESSION['success'] = 'Imagem enviada com sucesso!';
                } else {
                    $_SESSION['error'] = 'Erro ao fazer upload da imagem.';
                }
            } else {
                $_SESSION['error'] = 'Tipo de arquivo não permitido. Use apenas JPG, PNG, GIF ou WebP.';
            }
        }
    } elseif ($action === 'delete' && isset($_POST['id'])) {
        $id = $_POST['id'];

        // Buscar imagem para deletar do servidor
        $stmt = $conexao->prepare("SELECT imagem FROM slider_imagens WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $file_path = '../assets/img/slider/' . $row['imagem'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        $stmt->close();

        // Deletar do banco
        $stmt = $conexao->prepare("DELETE FROM slider_imagens WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = 'Imagem deletada com sucesso!';
    } elseif ($action === 'update' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $titulo = $_POST['titulo'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $ordem = $_POST['ordem'] ?? 0;
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        $stmt = $conexao->prepare("UPDATE slider_imagens SET titulo = ?, descricao = ?, ordem = ?, ativo = ? WHERE id = ?");
        $stmt->bind_param("ssiii", $titulo, $descricao, $ordem, $ativo, $id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = 'Imagem atualizada com sucesso!';
    }
}

// Estatísticas para os cards
$query_total = "SELECT COUNT(*) as total FROM slider_imagens";
$result_total = mysqli_query($conexao, $query_total);
$total_imagens = 0;
if ($result_total) {
    $row = mysqli_fetch_assoc($result_total);
    $total_imagens = $row ? $row['total'] : 0;
}

$query_ativas = "SELECT COUNT(*) as total FROM slider_imagens WHERE ativo = 1";
$result_ativas = mysqli_query($conexao, $query_ativas);
$imagens_ativas = 0;
if ($result_ativas) {
    $row = mysqli_fetch_assoc($result_ativas);
    $imagens_ativas = $row ? $row['total'] : 0;
}

$query_inativas = "SELECT COUNT(*) as total FROM slider_imagens WHERE ativo = 0";
$result_inativas = mysqli_query($conexao, $query_inativas);
$imagens_inativas = 0;
if ($result_inativas) {
    $row = mysqli_fetch_assoc($result_inativas);
    $imagens_inativas = $row ? $row['total'] : 0;
}

$query_hoje = "SELECT COUNT(*) as total FROM slider_imagens WHERE DATE(data_cadastro) = CURDATE()";
$result_hoje = mysqli_query($conexao, $query_hoje);
$imagens_hoje = 0;
if ($result_hoje) {
    $row = mysqli_fetch_assoc($result_hoje);
    $imagens_hoje = $row ? $row['total'] : 0;
}

// Buscar imagens do slider
$imagens = [];
$result = mysqli_query($conexao, "SELECT * FROM slider_imagens ORDER BY ordem ASC, data_cadastro DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $imagens[] = $row;
}

$is_iframe = isset($_GET['iframe']) || strpos($_SERVER['HTTP_REFERER'] ?? '', 'admin.php') !== false;
?>

<?php if (!$is_iframe): ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CONFINTER - Galeria de Imagens</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <style>
        .image-preview {
            max-width: 200px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .image-preview:hover {
            transform: scale(1.05);
        }
        .gallery-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .gallery-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        .status-badge {
            font-size: 0.8em;
            padding: 0.375rem 0.75rem;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        .upload-area:hover {
            border-color: #007bff;
            background: #e3f2fd;
        }
        .upload-area.dragover {
            border-color: #007bff;
            background: #e3f2fd;
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
                        <i class="fas fa-images mr-2"></i>
                        Galeria de Imagens
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
                        <li class="breadcrumb-item active">Galeria</li>
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
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo number_format($total_imagens); ?></h3>
                            <p>Total de Imagens</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-images"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo number_format($imagens_ativas); ?></h3>
                            <p>Imagens Ativas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo number_format($imagens_inativas); ?></h3>
                            <p>Imagens Inativas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-eye-slash"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo number_format($imagens_hoje); ?></h3>
                            <p>Adicionadas Hoje</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensagens -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-check-circle mr-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-triangle mr-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Upload Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-upload mr-1"></i>
                        Adicionar Nova Imagem
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form action="" method="post" enctype="multipart/form-data" id="uploadForm">
                        <input type="hidden" name="action" value="upload">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="titulo">
                                        <i class="fas fa-heading mr-1"></i>Título da Imagem
                                    </label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Digite o título da imagem">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ordem">
                                        <i class="fas fa-sort-numeric-up mr-1"></i>Ordem de Exibição
                                    </label>
                                    <input type="number" class="form-control" id="ordem" name="ordem" value="0" min="0" placeholder="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="descricao">
                                <i class="fas fa-align-left mr-1"></i>Descrição
                            </label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3" placeholder="Digite uma descrição para a imagem (opcional)"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="imagem">
                                <i class="fas fa-file-image mr-1"></i>Selecionar Imagem
                            </label>
                            <div class="upload-area" id="uploadArea">
                                <div class="mb-3">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <p class="mb-2">Arraste e solte uma imagem aqui ou clique para selecionar</p>
                                    <small class="text-muted">Formatos aceitos: JPG, PNG, GIF, WebP (máx. 5MB)</small>
                                </div>
                                <input type="file" class="d-none" id="imagem" name="imagem" accept="image/*" required>
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('imagem').click()">
                                    <i class="fas fa-folder-open mr-1"></i>Selecionar Arquivo
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="ativo" name="ativo" checked>
                                <label class="custom-control-label" for="ativo">
                                    <i class="fas fa-toggle-on mr-1"></i>Imagem ativa no carrossel do site
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-upload mr-2"></i>Enviar Imagem
                            </button>
                            <button type="reset" class="btn btn-secondary btn-lg ml-2">
                                <i class="fas fa-eraser mr-2"></i>Limpar Formulário
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Images Gallery -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-th mr-1"></i>
                        Galeria de Imagens (<?php echo count($imagens); ?>)
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($imagens)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-images fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted">Nenhuma imagem cadastrada</h4>
                            <p class="text-muted">Adicione imagens ao carrossel do site usando o formulário acima.</p>
                            <a href="#uploadForm" class="btn btn-primary">
                                <i class="fas fa-plus mr-1"></i>Adicionar Primeira Imagem
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($imagens as $imagem): ?>
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                    <div class="card gallery-card h-100">
                                        <div class="card-body text-center d-flex flex-column">
                                            <div class="mb-3">
                                                <img src="../assets/img/slider/<?php echo htmlspecialchars($imagem['imagem']); ?>"
                                                     alt="<?php echo htmlspecialchars($imagem['titulo']); ?>"
                                                     class="image-preview w-100">
                                            </div>
                                            <h6 class="card-title font-weight-bold mb-2">
                                                <?php echo htmlspecialchars($imagem['titulo']); ?>
                                            </h6>
                                            <?php if (!empty($imagem['descricao'])): ?>
                                                <p class="card-text text-muted small mb-3 grow">
                                                    <?php echo htmlspecialchars(substr($imagem['descricao'], 0, 80)); ?>
                                                    <?php if (strlen($imagem['descricao']) > 80): ?>...<?php endif; ?>
                                                </p>
                                            <?php endif; ?>
                                            <div class="mb-3">
                                                <span class="badge badge-<?php echo $imagem['ativo'] ? 'success' : 'secondary'; ?> status-badge mr-1">
                                                    <i class="fas fa-<?php echo $imagem['ativo'] ? 'eye' : 'eye-slash'; ?> mr-1"></i>
                                                    <?php echo $imagem['ativo'] ? 'Ativa' : 'Inativa'; ?>
                                                </span>
                                                <span class="badge badge-info status-badge">
                                                    <i class="fas fa-sort-numeric-up mr-1"></i>Ordem: <?php echo $imagem['ordem']; ?>
                                                </span>
                                            </div>
                                            <small class="text-muted mb-3">
                                                <i class="fas fa-calendar mr-1"></i>
                                                <?php echo date('d/m/Y H:i', strtotime($imagem['data_cadastro'])); ?>
                                            </small>
                                            <div class="btn-group w-100" role="group">
                                                <button class="btn btn-warning btn-sm flex-fill" onclick="editarImagem(<?php echo $imagem['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-info btn-sm flex-fill" onclick="visualizarImagem('../assets/img/slider/<?php echo htmlspecialchars($imagem['imagem']); ?>', '<?php echo htmlspecialchars($imagem['titulo']); ?>')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm flex-fill" onclick="deletarImagem(<?php echo $imagem['id']; ?>, '<?php echo htmlspecialchars($imagem['titulo']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include 'footer.php'; ?>

<!-- Modal para Visualizar Imagem -->
<div class="modal fade" id="visualizarModal" tabindex="-1" role="dialog" aria-labelledby="visualizarModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="visualizarModalLabel">
                    <i class="fas fa-image mr-2"></i>Visualizar Imagem
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="imagemVisualizar" src="" alt="" class="img-fluid rounded" style="max-height: 70vh;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    // Drag and drop functionality
    var $uploadArea = $('#uploadArea');
    var $fileInput = $('#imagem');

    $uploadArea.on('dragover dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $uploadArea.addClass('dragover');
    });

    $uploadArea.on('dragleave dragend', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $uploadArea.removeClass('dragover');
    });

    $uploadArea.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $uploadArea.removeClass('dragover');

        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            $fileInput[0].files = files;
            updateFileName(files[0].name);
        }
    });

    $fileInput.on('change', function() {
        if (this.files.length > 0) {
            updateFileName(this.files[0].name);
        }
    });

    function updateFileName(fileName) {
        $uploadArea.find('p').html('<strong>Arquivo selecionado:</strong><br>' + fileName);
        $uploadArea.find('small').hide();
    }
});

function visualizarImagem(src, titulo) {
    document.getElementById('imagemVisualizar').src = src;
    document.getElementById('visualizarModalLabel').innerHTML = '<i class="fas fa-image mr-2"></i>' + titulo;
    $('#visualizarModal').modal('show');
}

function editarImagem(id) {
    alert('Funcionalidade de edição será implementada em breve. ID: ' + id);
}

function deletarImagem(id, titulo) {
    if (confirm('Tem certeza que deseja deletar a imagem "' + titulo + '"?\n\nEsta ação não pode ser desfeita.')) {
        var form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

</body>
</html>
<?php endif; ?>

<?php
$conexao->close();
?>