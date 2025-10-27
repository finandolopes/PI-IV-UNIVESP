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

// Processar reset de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $nova_senha = trim($_POST['nova_senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);
    
    $errors = [];
    $success = '';
    
    // Validações
    if (empty($user_id)) {
        $errors[] = 'Usuário não selecionado';
    }
    
    if (empty($nova_senha)) {
        $errors[] = 'Nova senha é obrigatória';
    } elseif (strlen($nova_senha) < 6) {
        $errors[] = 'A senha deve ter pelo menos 6 caracteres';
    }
    
    if ($nova_senha !== $confirmar_senha) {
        $errors[] = 'As senhas não coincidem';
    }
    
    if (empty($errors)) {
        // Resetar senha
        $nova_senha_hash = md5($nova_senha);
        $stmt = $conexao->prepare('UPDATE adm SET senha = ? WHERE id_usuario = ?');
        $stmt->bind_param('si', $nova_senha_hash, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Senha resetada com sucesso!';
            header('Location: reset_senha.php');
            exit();
        } else {
            $errors[] = 'Erro ao resetar senha: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// Buscar usuários para seleção
$usuarios = [];
$stmt = $conexao->prepare('SELECT id_usuario AS id, nome, email, perfil AS tipo FROM adm ORDER BY nome');
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}
$stmt->close();
?>
<?php if (!$is_iframe): ?>
<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Reset de Senha - CONFINTER</title>

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
    <title>Reset de Senha - CONFINTER</title>
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
                        <i class='fas fa-key mr-2'></i>
                        Reset de Senha
                    </h1>
                </div>
                <div class='col-sm-6'>
                    <ol class='breadcrumb float-sm-right'>
                        <li class='breadcrumb-item'><a href='admin.php'>Dashboard</a></li>
                        <li class='breadcrumb-item active'>Reset Senha</li>
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
                <div class='col-md-8 offset-md-2'>
                    <div class='card card-warning'>
                        <div class='card-header'>
                            <h3 class='card-title'>
                                <i class='fas fa-key mr-1'></i>
                                Resetar Senha de Usuário
                            </h3>
                        </div>
                        <form method='post' action=''>
                            <div class='card-body'>
                                <div class='alert alert-info'>
                                    <i class='fas fa-info-circle mr-1'></i>
                                    <strong>Atenção:</strong> Esta ação irá alterar a senha do usuário selecionado. 
                                    Certifique-se de informar a nova senha ao usuário após o reset.
                                </div>

                                <div class='form-group'>
                                    <label for='user_id'>Selecionar Usuário *</label>
                                    <select class='form-control' id='user_id' name='user_id' required>
                                        <option value=''>Selecione um usuário...</option>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <option value='<?php echo $usuario['id']; ?>' 
                                                    <?php echo (isset($_POST['user_id']) && $_POST['user_id'] == $usuario['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($usuario['nome']); ?> 
                                                (<?php echo htmlspecialchars($usuario['email']); ?>) - 
                                                <?php echo $usuario['tipo'] === 'admin' ? 'Administrador' : 'Usuário'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class='form-text text-muted'>Usuário que terá a senha resetada</small>
                                </div>

                                <div class='form-group'>
                                    <label for='nova_senha'>Nova Senha *</label>
                                    <input type='password' class='form-control' id='nova_senha' name='nova_senha' 
                                           required minlength='6' maxlength='50'>
                                    <small class='form-text text-muted'>Mínimo 6 caracteres</small>
                                </div>

                                <div class='form-group'>
                                    <label for='confirmar_senha'>Confirmar Nova Senha *</label>
                                    <input type='password' class='form-control' id='confirmar_senha' name='confirmar_senha' 
                                           required minlength='6' maxlength='50'>
                                    <small class='form-text text-muted'>Repita a nova senha</small>
                                </div>

                                <!-- Gerador de senha -->
                                <div class='form-group'>
                                    <label>Gerador de Senha:</label>
                                    <div class='input-group'>
                                        <input type='text' class='form-control' id='senha_gerada' readonly>
                                        <div class='input-group-append'>
                                            <button type='button' class='btn btn-outline-secondary' id='gerar_senha'>
                                                <i class='fas fa-random'></i> Gerar
                                            </button>
                                            <button type='button' class='btn btn-outline-primary' id='usar_senha'>
                                                <i class='fas fa-check'></i> Usar
                                            </button>
                                        </div>
                                    </div>
                                    <small class='form-text text-muted'>Clique em "Gerar" para criar uma senha segura</small>
                                </div>
                            </div>

                            <div class='card-footer'>
                                <button type='submit' class='btn btn-warning'>
                                    <i class='fas fa-key'></i> Resetar Senha
                                </button>
                                <a href='#' onclick='window.top.backToDashboard()' class='btn btn-secondary ml-2'>
                                    <i class='fas fa-times'></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Lista de Usuários -->
                    <div class='card card-info'>
                        <div class='card-header'>
                            <h3 class='card-title'>
                                <i class='fas fa-users mr-1'></i>
                                Usuários do Sistema
                            </h3>
                        </div>
                        <div class='card-body table-responsive p-0'>
                            <table class='table table-hover text-nowrap'>
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Tipo</th>
                                        <th>Último Login</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                            <td>
                                                <?php if ($usuario['tipo'] === 'admin'): ?>
                                                    <span class='badge badge-danger'>Admin</span>
                                                <?php else: ?>
                                                    <span class='badge badge-info'>Usuário</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $stmt_login = $conexao->prepare('SELECT data_cadastro FROM adm WHERE id_usuario = ?');
                                                $stmt_login->bind_param('i', $usuario['id']);
                                                $stmt_login->execute();
                                                $result_login = $stmt_login->get_result();
                                                if ($row_login = $result_login->fetch_assoc()) {
                                                    echo $row_login['data_cadastro'] ? date('d/m/Y H:i', strtotime($row_login['data_cadastro'])) : 'Nunca';
                                                }
                                                $stmt_login->close();
                                                ?>
                                            </td>
                                            <td>
                                                <button class='btn btn-sm btn-warning' onclick='selecionarUsuario(<?php echo $usuario['id']; ?>)'>
                                                    <i class='fas fa-key'></i> Reset
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
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
        var senha = $('#nova_senha').val();
        var confirmar = $(this).val();
        
        if (senha !== confirmar && confirmar.length > 0) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Gerador de senha
    $('#gerar_senha').on('click', function() {
        var senha = gerarSenha(12);
        $('#senha_gerada').val(senha);
    });

    $('#usar_senha').on('click', function() {
        var senha = $('#senha_gerada').val();
        if (senha) {
            $('#nova_senha').val(senha);
            $('#confirmar_senha').val(senha);
            $('#confirmar_senha').removeClass('is-invalid');
        }
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

function gerarSenha(comprimento) {
    var caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    var senha = '';
    for (var i = 0; i < comprimento; i++) {
        senha += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
    }
    return senha;
}

function selecionarUsuario(userId) {
    $('#user_id').val(userId);
    // Scroll to top
    $('html, body').animate({ scrollTop: 0 }, 'slow');
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
<?php
$conexao->close();
?>
