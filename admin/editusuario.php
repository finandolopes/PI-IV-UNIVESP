<?php
include_once('../php/conexao.php');
include_once('../php/funcoes_usuarios.php');

// Verificar se o usuário está logado
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Verifica se o ID do usuário foi fornecido na URL
if(isset($_GET['id'])) {
    $id = $_GET['id'];
    $usuario = buscarUsuarioPorId($conexao, $id);
    if(!$usuario) {
        header("Location: usuarios.php");
        exit();
    }
} else {
    header("Location: usuarios.php");
    exit();
}

// Processamento do formulário de edição
if(isset($_POST['editar_usuario'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $usuario = $_POST['usuario'];
    $email = $_POST['email'];

    // Verifica se a senha foi alterada
    $senha = !empty($_POST['senha']) ? $_POST['senha'] : null;

    // Validar entrada (exemplo: campos não vazios)
    if(empty($nome) || empty($usuario) || empty($email)) {
        $error = "Por favor, preencha todos os campos.";
    } else {
        // Prevenir SQL Injection usando prepared statements
        editarUsuario($conexao, $id, $nome, $usuario, $senha, $email);
        header("Location: usuarios.php?edit_success=true");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Usuário - CONFINTER</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                        <i class="fas fa-user-edit mr-2"></i>
                        Editar Usuário
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admin.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="usuarios.php">Usuários</a></li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Editar Dados do Usuário</h3>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nome">Nome Completo</label>
                                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="usuario">Nome de Usuário</label>
                                    <input type="text" class="form-control" id="usuario" name="usuario" value="<?php echo htmlspecialchars($usuario['usuario']); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">E-mail</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefone">Telefone</label>
                                    <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="senha">Nova Senha (deixe em branco para manter a atual)</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="senha" name="senha">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
                                    </span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Deixe em branco se não quiser alterar a senha</small>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="editar_usuario" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                            <a href="usuarios.php" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include 'footer.php'; ?>

<script>
function togglePassword() {
    var passwordField = document.getElementById('senha');
    var toggleIcon = document.querySelector('.toggle-password');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>

</body>
</html>