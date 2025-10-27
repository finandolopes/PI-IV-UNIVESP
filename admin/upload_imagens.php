<?php
session_start();
include_once('../php/conexao.php');

// Detectar se está em iframe
$is_iframe = isset($_GET['iframe']) || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin.php') !== false);

// Verifica se o usuário está logado
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Processar upload de imagem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagem'])) {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    
    $errors = [];
    $success = '';
    
    // Validações
    if (empty($titulo)) {
        $errors[] = 'Título é obrigatório';
    }
    
    // Verificar se arquivo foi enviado
    if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Erro no upload do arquivo';
    } else {
        $file = $_FILES['imagem'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = 'Tipo de arquivo não permitido. Use apenas JPG, PNG, GIF ou WebP';
        }
        
        if ($file['size'] > $max_size) {
            $errors[] = 'Arquivo muito grande. Máximo 5MB';
        }
    }
    
    if (empty($errors)) {
        // Criar diretório se não existir
        $upload_dir = '../assets/img/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Gerar nome único para o arquivo
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('img_', true) . '.' . $ext;
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Salvar no banco de dados
            $stmt = $conexao->prepare('INSERT INTO imagens_carrossel (titulo, descricao, nome_arquivo, data_upload) VALUES (?, ?, ?, NOW())');
            $stmt->bind_param('sss', $titulo, $descricao, $filename);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Imagem enviada com sucesso!';
                header('Location: upload_imagens.php');
                exit();
            } else {
                $errors[] = 'Erro ao salvar no banco de dados: ' . $stmt->error;
                // Remover arquivo se erro no BD
                unlink($filepath);
            }
            $stmt->close();
        } else {
            $errors[] = 'Erro ao mover arquivo para o servidor';
        }
    }
}

// Buscar imagens existentes
$imagens = [];
$stmt = $conexao->prepare('SELECT id, titulo, descricao, nome_arquivo, data_upload FROM imagens_carrossel ORDER BY data_upload DESC LIMIT 20');
if ($stmt->execute()) {
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $imagens[] = $row;
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Upload de Imagens - CONFINTER</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback'>
    <!-- Font Awesome -->
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <!-- Theme style -->
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css'>
    <!-- Custom Admin CSS -->
    <link rel='stylesheet' href='assets/css/custom-admin.css'>
</head>
<?php if (!$is_iframe): ?>
<body class='hold-transition sidebar-mini layout-fixed'>
<div class='wrapper'>

<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>

<!-- Content Wrapper -->
<div class='content-wrapper'>
<?php else: ?>
<body>
<div class='container-fluid'>
<?php endif; ?>
    <!-- Content Header -->
    <div class='content-header'>
        <div class='container-fluid'>
            <div class='row mb-2'>
                <div class='col-sm-6'>
                    <h1 class='m-0'>Upload de Imagens</h1>
                </div>
                <div class='col-sm-6'>
                    <ol class='breadcrumb float-sm-right'>
                        <li class='breadcrumb-item'><a href='admin.php'>Home</a></li>
                        <li class='breadcrumb-item active'>Upload Imagens</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class='content'>
        <div class='container-fluid'>
            <!-- Alertas -->
            <?php if (!empty($errors)): ?>
                <div class='alert alert-danger alert-dismissible'>
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                    <h5><i class='icon fas fa-ban'></i> Erros encontrados:</h5>
                    <ul class='mb-0'>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class='alert alert-success alert-dismissible'>
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                    <h5><i class='icon fas fa-check'></i> Sucesso!</h5>
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <div class='row'>
                <!-- Formulário de Upload -->
                <div class='col-md-6'>
                    <div class='card card-primary'>
                        <div class='card-header'>
                            <h3 class='card-title'>
                                <i class='fas fa-upload mr-1'></i>
                                Enviar Nova Imagem
                            </h3>
                        </div>
                        <form method='post' action='' enctype='multipart/form-data'>
                            <div class='card-body'>
                                <div class='form-group'>
                                    <label for='titulo'>Título *</label>
                                    <input type='text' class='form-control' id='titulo' name='titulo' 
                                           value='<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>' 
                                           required maxlength='100'>
                                </div>

                                <div class='form-group'>
                                    <label for='descricao'>Descrição</label>
                                    <textarea class='form-control' id='descricao' name='descricao' rows='3' maxlength='255'><?php echo isset($_POST['descricao']) ? htmlspecialchars($_POST['descricao']) : ''; ?></textarea>
                                </div>

                                <div class='form-group'>
                                    <label for='imagem'>Arquivo de Imagem *</label>
                                    <div class='custom-file'>
                                        <input type='file' class='custom-file-input' id='imagem' name='imagem' 
                                               accept='image/*' required>
                                        <label class='custom-file-label' for='imagem'>Escolher arquivo...</label>
                                    </div>
                                    <small class='form-text text-muted'>Formatos aceitos: JPG, PNG, GIF, WebP. Máximo 5MB.</small>
                                </div>

                                <!-- Preview da imagem -->
                                <div class='form-group' id='preview-container' style='display: none;'>
                                    <label>Preview:</label>
                                    <div class='text-center'>
                                        <img id='preview' src='' alt='Preview' class='img-thumbnail' style='max-width: 200px; max-height: 200px;'>
                                    </div>
                                </div>
                            </div>

                            <div class='card-footer'>
                                <button type='submit' class='btn btn-primary'>
                                    <i class='fas fa-upload'></i> Enviar Imagem
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Galeria de Imagens -->
                <div class='col-md-6'>
                    <div class='card card-success'>
                        <div class='card-header'>
                            <h3 class='card-title'>
                                <i class='fas fa-images mr-1'></i>
                                Imagens Recentes
                            </h3>
                        </div>
                        <div class='card-body'>
                            <?php if (empty($imagens)): ?>
                                <p class='text-muted'>Nenhuma imagem enviada ainda.</p>
                            <?php else: ?>
                                <div class='row'>
                                    <?php foreach ($imagens as $imagem): ?>
                                        <div class='col-6 mb-3'>
                                            <div class='card'>
                                                <div class='card-body p-2'>
                                                    <img src='../assets/img/uploads/<?php echo htmlspecialchars($imagem['nome_arquivo']); ?>' 
                                                         alt='<?php echo htmlspecialchars($imagem['titulo']); ?>' 
                                                         class='img-fluid rounded'>
                                                    <h6 class='mt-2 mb-1'><?php echo htmlspecialchars($imagem['titulo']); ?></h6>
                                                    <small class='text-muted'><?php echo htmlspecialchars($imagem['descricao'] ?? 'Sem descrição'); ?></small>
                                                    <div class='mt-2'>
                                                        <button class='btn btn-sm btn-danger' onclick='deletarImagem(<?php echo $imagem['id']; ?>)'>
                                                            <i class='fas fa-trash'></i>
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

<script>
$(document).ready(function() {
    // Preview da imagem
    $('#imagem').on('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#preview').attr('src', e.target.result);
                $('#preview-container').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#preview-container').hide();
        }
    });

    // Atualizar label do file input
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

function deletarImagem(id) {
    if (confirm('Tem certeza que deseja excluir esta imagem?')) {
        window.location.href = 'deletar_imagem.php?id=' + id;
    }
}
</script>

</body>
</html>
<?php
$conexao->close();
?>
