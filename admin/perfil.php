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

// Buscar dados do usuário atual
$usuario = null;
$stmt = $conexao->prepare('SELECT id_usuario, nome, email, perfil as tipo, data_cadastro, avatar FROM adm WHERE usuario = ?');
$stmt->bind_param('s', $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $usuario = $row;
}
$stmt->close();

// Processar atualização do perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha_atual = trim($_POST['senha_atual']);
    $nova_senha = trim($_POST['nova_senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);
    $avatar_tipo = $_POST['avatar_tipo'] ?? 'upload';
    $avatar_padrao = $_POST['avatar_padrao'] ?? '';
    
    $errors = [];
    $success = '';
    
    // Processar upload de avatar
    $avatar_path = $usuario['avatar'] ?? ''; // Manter atual se não alterar
    
    if ($avatar_tipo === 'upload' && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/img/perfil/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = 'Tipo de arquivo não permitido. Use apenas JPG, PNG ou GIF.';
        } elseif ($_FILES['avatar']['size'] > 2 * 1024 * 1024) { // 2MB
            $errors[] = 'Arquivo muito grande. Máximo 2MB.';
        } else {
            $new_filename = 'avatar_' . $usuario['id_usuario'] . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                // Remover avatar anterior se existir
                if (!empty($usuario['avatar']) && file_exists('../' . $usuario['avatar'])) {
                    unlink('../' . $usuario['avatar']);
                }
                $avatar_path = 'assets/img/perfil/' . $new_filename;
            } else {
                $errors[] = 'Erro ao fazer upload do avatar.';
            }
        }
    } elseif ($avatar_tipo === 'padrao' && !empty($avatar_padrao)) {
        // Remover avatar anterior se existir
        if (!empty($usuario['avatar']) && file_exists('../' . $usuario['avatar'])) {
            unlink('../' . $usuario['avatar']);
        }
        $avatar_path = $avatar_padrao;
    }
    
    // Validações
    if (empty($nome)) {
        $errors[] = 'Nome é obrigatório';
    }
    
    if (empty($email)) {
        $errors[] = 'Email é obrigatório';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    } else {
        // Verificar se email já existe (exceto o atual)
        $stmt = $conexao->prepare('SELECT id FROM adm WHERE email = ? AND id_usuario != ?');
        $stmt->bind_param('si', $email, $usuario['id_usuario']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Este email já está cadastrado por outro usuário';
        }
        $stmt->close();
    }
    
    // Se vai alterar senha
    if (!empty($nova_senha)) {
        if (empty($senha_atual)) {
            $errors[] = 'Para alterar a senha, informe a senha atual';
        } elseif (!password_verify($senha_atual, $usuario['senha'] ?? md5($senha_atual))) {
            $errors[] = 'Senha atual incorreta';
        } elseif (strlen($nova_senha) < 6) {
            $errors[] = 'A nova senha deve ter pelo menos 6 caracteres';
        } elseif ($nova_senha !== $confirmar_senha) {
            $errors[] = 'As senhas não coincidem';
        }
    }
    
    if (empty($errors)) {
        // Atualizar dados básicos
        $stmt = $conexao->prepare('UPDATE adm SET nome = ?, email = ?, avatar = ? WHERE id_usuario = ?');
        $stmt->bind_param('sssi', $nome, $email, $avatar_path, $usuario['id_usuario']);
        
        if ($stmt->execute()) {
            // Atualizar senha se fornecida
            if (!empty($nova_senha)) {
                $nova_senha_hash = md5($nova_senha);
                $stmt_senha = $conexao->prepare('UPDATE adm SET senha = ? WHERE id_usuario = ?');
                $stmt_senha->bind_param('si', $nova_senha_hash, $usuario['id_usuario']);
                $stmt_senha->execute();
                $stmt_senha->close();
            }
            
            $_SESSION['username'] = $email; // Atualizar sessão se email mudou
            $_SESSION['success'] = 'Perfil atualizado com sucesso!';
            header('Location: perfil.php');
            exit();
        } else {
            $errors[] = 'Erro ao atualizar perfil: ' . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<?php if (!$is_iframe): ?>
<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Meu Perfil - CONFINTER</title>

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
<div class='wrapper'>

<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>
<?php else: ?>
<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Meu Perfil - CONFINTER</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <style>
        body { background: #f4f6f9; margin: 0; padding: 20px; }
        .content-wrapper { margin: 0; background: transparent; }
        .card { box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
    </style>
</head>
<body>
<?php endif; ?>

<!-- Content Wrapper -->
<div class='content-wrapper'>
    <!-- Content Header -->
    <div class='content-header'>
        <div class='container-fluid'>
            <div class='row mb-2'>
                <div class='col-sm-6'>
                    <h1 class='m-0'>
                        <i class='fas fa-user mr-2'></i>
                        Meu Perfil
                    </h1>
                </div>
                <div class='col-sm-6'>
                    <ol class='breadcrumb float-sm-right'>
                        <li class='breadcrumb-item'><a href='admin.php'>Dashboard</a></li>
                        <li class='breadcrumb-item active'>Perfil</li>
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
                <!-- Informações do Perfil -->
                <div class='col-md-4'>
                    <div class='card card-primary card-outline'>
                        <div class='card-body box-profile'>
                            <div class='text-center'>
                                <?php if (!empty($usuario['avatar'])): ?>
                                    <?php if (strpos($usuario['avatar'], 'assets/img/avatar/') === 0): ?>
                                        <img class='profile-user-img img-fluid img-circle' src='../<?php echo htmlspecialchars($usuario['avatar']); ?>' alt='Avatar do usuário'>
                                    <?php else: ?>
                                        <img class='profile-user-img img-fluid img-circle' src='../<?php echo htmlspecialchars($usuario['avatar']); ?>' alt='Avatar do usuário'>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class='fas fa-user-circle fa-5x text-primary mb-3'></i>
                                <?php endif; ?>
                            </div>

                            <h3 class='profile-username text-center'><?php echo htmlspecialchars($usuario['nome']); ?></h3>

                            <p class='text-muted text-center'>
                                <?php 
                                if ($usuario['tipo'] === 'admin') {
                                    echo '<span class="badge badge-danger">Administrador</span>';
                                } else {
                                    echo '<span class="badge badge-info">Usuário</span>';
                                }
                                ?>
                            </p>

                            <ul class='list-group list-group-unbordered mb-3'>
                                <li class='list-group-item'>
                                    <b>Email</b> <a class='float-right'><?php echo htmlspecialchars($usuario['email']); ?></a>
                                </li>
                                <li class='list-group-item'>
                                    <b>Cadastrado em</b> <a class='float-right'><?php echo date('d/m/Y', strtotime($usuario['data_cadastro'])); ?></a>
                                </li>
                                <li class='list-group-item'>
                                    <b>Último Login</b> <a class='float-right'>
                                        <?php echo (isset($usuario['ultimo_login']) && $usuario['ultimo_login']) ? date('d/m/Y H:i', strtotime($usuario['ultimo_login'])) : 'Nunca'; ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Formulário de Edição -->
                <div class='col-md-8'>
                    <div class='card card-primary'>
                        <div class='card-header'>
                            <h3 class='card-title'>
                                <i class='fas fa-edit mr-1'></i>
                                Editar Perfil
                            </h3>
                        </div>
                        <form method='post' action='' enctype='multipart/form-data'>
                            <div class='card-body'>
                                <div class='form-group'>
                                    <label for='nome'>Nome Completo *</label>
                                    <input type='text' class='form-control' id='nome' name='nome' 
                                           value='<?php echo htmlspecialchars($usuario['nome']); ?>' 
                                           required maxlength='100'>
                                    <small class='form-text text-muted'>Seu nome completo</small>
                                </div>

                                <div class='form-group'>
                                    <label for='email'>Email *</label>
                                    <input type='email' class='form-control' id='email' name='email' 
                                           value='<?php echo htmlspecialchars($usuario['email']); ?>' 
                                           required maxlength='100'>
                                    <small class='form-text text-muted'>Email válido para login no sistema</small>
                                </div>

                                <hr>
                                <h5>Avatar do Perfil</h5>
                                
                                <div class='form-group'>
                                    <label>Tipo de Avatar</label>
                                    <div class='form-check'>
                                        <input class='form-check-input' type='radio' name='avatar_tipo' id='avatar_upload' value='upload' checked>
                                        <label class='form-check-label' for='avatar_upload'>
                                            Upload de Imagem
                                        </label>
                                    </div>
                                    <div class='form-check'>
                                        <input class='form-check-input' type='radio' name='avatar_tipo' id='avatar_padrao' value='padrao'>
                                        <label class='form-check-label' for='avatar_padrao'>
                                            Avatar Padrão
                                        </label>
                                    </div>
                                </div>

                                <div id='upload_section'>
                                    <div class='form-group'>
                                        <label for='avatar'>Selecionar Imagem</label>
                                        <input type='file' class='form-control-file' id='avatar' name='avatar' accept='image/*'>
                                        <small class='form-text text-muted'>Formatos aceitos: JPG, PNG, GIF. Máximo 2MB.</small>
                                    </div>
                                </div>

                                <div id='padrao_section' style='display: none;'>
                                    <div class='form-group'>
                                        <label>Escolher Avatar Padrão</label>
                                        <div class='row'>
                                            <?php
                                            $avatares_padrao = [
                                                'assets/img/avatar/avatar1.png',
                                                'assets/img/avatar/avatar2.png', 
                                                'assets/img/avatar/avatar3.png',
                                                'assets/img/avatar/avatar4.png',
                                                'assets/img/avatar/avatar5.png',
                                                'assets/img/avatar/avatar6.png'
                                            ];
                                            foreach ($avatares_padrao as $index => $avatar): ?>
                                                <div class='col-4 mb-3'>
                                                    <div class='text-center'>
                                                        <img src='../<?php echo $avatar; ?>' class='img-circle' style='width: 60px; height: 60px; cursor: pointer;' 
                                                             onclick="selecionarAvatar('<?php echo $avatar; ?>', this)">
                                                        <br>
                                                        <input type='radio' name='avatar_padrao' value='<?php echo $avatar; ?>' style='display: none;' id='avatar_<?php echo $index; ?>'>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <h5>Alterar Senha (opcional)</h5>
                                <small class='form-text text-muted mb-3'>Deixe em branco se não quiser alterar a senha</small>

                                <div class='form-group'>
                                    <label for='senha_atual'>Senha Atual</label>
                                    <input type='password' class='form-control' id='senha_atual' name='senha_atual' 
                                           maxlength='50'>
                                    <small class='form-text text-muted'>Necessária para alterar a senha</small>
                                </div>

                                <div class='form-group'>
                                    <label for='nova_senha'>Nova Senha</label>
                                    <input type='password' class='form-control' id='nova_senha' name='nova_senha' 
                                           minlength='6' maxlength='50'>
                                    <small class='form-text text-muted'>Mínimo 6 caracteres</small>
                                </div>

                                <div class='form-group'>
                                    <label for='confirmar_senha'>Confirmar Nova Senha</label>
                                    <input type='password' class='form-control' id='confirmar_senha' name='confirmar_senha' 
                                           minlength='6' maxlength='50'>
                                    <small class='form-text text-muted'>Repita a nova senha</small>
                                </div>
                            </div>

                            <div class='card-footer'>
                                <button type='submit' class='btn btn-primary'>
                                    <i class='fas fa-save'></i> Salvar Alterações
                                </button>
                                <a href='#' onclick='window.top.backToDashboard()' class='btn btn-secondary ml-2'>
                                    <i class='fas fa-times'></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php if (!$is_iframe): ?>
<?php include 'footer.php'; ?>
<?php endif; ?>

<?php if (!$is_iframe): ?>
</div>
<?php endif; ?>

<!-- jQuery -->
<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
<!-- Bootstrap 4 -->
<script src='https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js'></script>
<!-- AdminLTE App -->
<script src='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js'></script>

<script>
$(document).ready(function() {
    // Validação de confirmação de senha
    $('#confirmar_senha').on('keyup', function() {
        var nova_senha = $('#nova_senha').val();
        var confirmar = $(this).val();
        
        if (nova_senha !== confirmar && confirmar.length > 0) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Alternar entre upload e avatar padrão
    $('input[name="avatar_tipo"]').on('change', function() {
        if ($(this).val() === 'upload') {
            $('#upload_section').show();
            $('#padrao_section').hide();
        } else {
            $('#upload_section').hide();
            $('#padrao_section').show();
        }
    });

    // Função para selecionar avatar padrão
    window.selecionarAvatar = function(avatarPath, element) {
        $('input[name="avatar_padrao"]').prop('checked', false);
        $(element).siblings('input[type="radio"]').prop('checked', true);
        
        // Visual feedback
        $('.profile-user-img').removeClass('border border-primary');
        $(element).addClass('border border-primary');
    };
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
<?php
$conexao->close();
?>
