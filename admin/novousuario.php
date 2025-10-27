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

// Processar criação de usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);
    $tipo = $_POST['tipo'];
    
    $errors = [];
    
    // Validações
    if (empty($nome)) {
        $errors[] = 'Nome é obrigatório';
    }
    
    if (empty($email)) {
        $errors[] = 'Email é obrigatório';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    } else {
        // Verificar se email já existe
        $stmt = $conexao->prepare('SELECT id FROM adm WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Este email já está cadastrado';
        }
        $stmt->close();
    }
    
    if (empty($senha)) {
        $errors[] = 'Senha é obrigatória';
    } elseif (strlen($senha) < 6) {
        $errors[] = 'A senha deve ter pelo menos 6 caracteres';
    }
    
    if ($senha !== $confirmar_senha) {
        $errors[] = 'As senhas não coincidem';
    }
    
    if (empty($tipo) || !in_array($tipo, ['admin', 'user'])) {
        $errors[] = 'Tipo de usuário inválido';
    }
    
    if (empty($errors)) {
        // Criar usuário
        $senha_hash = md5($senha);
        $stmt = $conexao->prepare('INSERT INTO adm (nome, email, senha, tipo, data_cadastro) VALUES (?, ?, ?, ?, NOW())');
        $stmt->bind_param('ssss', $nome, $email, $senha_hash, $tipo);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Usuário criado com sucesso!';
            header('Location: listarusuario.php');
            exit();
        } else {
            $errors[] = 'Erro ao criar usuário: ' . $stmt->error;
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
    <title>Novo Usuário - CONFINTER</title>

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
    <title>Novo Usuário - CONFINTER</title>
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
                        <i class='fas fa-user-plus mr-2'></i>
                        Novo Usuário
                    </h1>
                </div>
                <div class='col-sm-6'>
                    <ol class='breadcrumb float-sm-right'>
                        <li class='breadcrumb-item'><a href='admin.php'>Dashboard</a></li>
                        <li class='breadcrumb-item'><a href='listarusuario.php'>Usuários</a></li>
                        <li class='breadcrumb-item active'>Novo</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class='content'>
        <div class='container-fluid'>
            <!-- Alertas de erro -->
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

            <!-- Formulário -->
            <div class='row'>
                <div class='col-md-8 offset-md-2'>
                    <div class='card card-primary'>
                        <div class='card-header'>
                            <h3 class='card-title'>
                                <i class='fas fa-user-plus mr-1'></i>
                                Cadastrar Novo Usuário
                            </h3>
                        </div>
                        <form method='post' action=''>
                            <div class='card-body'>
                                <div class='form-group'>
                                    <label for='nome'>Nome Completo *</label>
                                    <input type='text' class='form-control' id='nome' name='nome' 
                                           value='<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>' 
                                           required maxlength='100'>
                                    <small class='form-text text-muted'>Nome completo do usuário</small>
                                </div>

                                <div class='form-group'>
                                    <label for='email'>Email *</label>
                                    <input type='email' class='form-control' id='email' name='email' 
                                           value='<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>' 
                                           required maxlength='100'>
                                    <small class='form-text text-muted'>Email válido para login no sistema</small>
                                </div>

                                <div class='form-group'>
                                    <label for='tipo'>Tipo de Usuário *</label>
                                    <select class='form-control' id='tipo' name='tipo' required>
                                        <option value=''>Selecione...</option>
                                        <option value='user' <?php echo (isset($_POST['tipo']) && $_POST['tipo'] === 'user') ? 'selected' : ''; ?>>Usuário</option>
                                        <option value='admin' <?php echo (isset($_POST['tipo']) && $_POST['tipo'] === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                    </select>
                                    <small class='form-text text-muted'>Administradores têm acesso total ao sistema</small>
                                </div>

                                <div class='form-group'>
                                    <label for='senha'>Senha *</label>
                                    <input type='password' class='form-control' id='senha' name='senha' 
                                           required minlength='6' maxlength='50'>
                                    <small class='form-text text-muted'>Mínimo 6 caracteres</small>
                                </div>

                                <div class='form-group'>
                                    <label for='confirmar_senha'>Confirmar Senha *</label>
                                    <input type='password' class='form-control' id='confirmar_senha' name='confirmar_senha' 
                                           required minlength='6' maxlength='50'>
                                    <small class='form-text text-muted'>Repita a senha</small>
                                </div>
                            </div>

                            <div class='card-footer'>
                                <button type='submit' class='btn btn-primary'>
                                    <i class='fas fa-save'></i> Criar Usuário
                                </button>
                                <a href='listarusuario.php' class='btn btn-secondary ml-2'>
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
        var senha = $('#senha').val();
        var confirmar = $(this).val();
        
        if (senha !== confirmar && confirmar.length > 0) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
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
