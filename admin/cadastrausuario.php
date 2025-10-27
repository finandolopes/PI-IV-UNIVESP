<?php
session_start();
include_once(__DIR__ . '/../php/conexao.php');

// Detectar se está em iframe
$is_iframe = isset($_GET['iframe']) || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin.php') !== false);

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Função para criar um novo usuário
function criarUsuario($conexao, $nome, $usuario, $senha, $telefone, $email, $perfil) {
    // Hash da senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // Query SQL para inserir o novo usuário
    $sql = "INSERT INTO usuarios (nome, usuario, senha, telefone, email, perfil, data_cadastro) VALUES (?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ssssss", $nome, $usuario, $senhaHash, $telefone, $email, $perfil);

    // Executa a query
    if ($stmt->execute()) {
        $stmt->close();
        return true; // Retorna verdadeiro se a inserção for bem-sucedida
    } else {
        $stmt->close();
        return false; // Retorna falso se houver algum erro na inserção
    }
}

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Obtém os valores do formulário
    $nome = trim($_POST["nome"]);
    $usuario = trim($_POST["usuario"]);
    $senha = $_POST["senha"];
    $telefone = trim($_POST["telefone"]);
    $email = trim($_POST["email"]);
    $perfil = $_POST["perfil"];

    // Validações básicas
    $errors = [];
    if (empty($nome)) $errors[] = "Nome é obrigatório";
    if (empty($usuario)) $errors[] = "Usuário é obrigatório";
    if (empty($senha)) $errors[] = "Senha é obrigatória";
    if (empty($email)) $errors[] = "Email é obrigatório";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email inválido";

    if (empty($errors)) {
        // Chama a função criarUsuario para inserir o novo usuário no banco de dados
        if (criarUsuario($conexao, $nome, $usuario, $senha, $telefone, $email, $perfil)) {
            $_SESSION['success'] = "Usuário criado com sucesso!";
            header('Location: cadastrausuario.php' . ($is_iframe ? '?iframe=1' : ''));
            exit();
        } else {
            $_SESSION['error'] = "Erro ao criar usuário: " . mysqli_error($conexao);
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}
?>

<?php if (!$is_iframe): ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Usuário - CONFINTER</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                        <i class="fas fa-user-plus mr-2"></i>
                        Cadastrar Novo Usuário
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admin.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="usuarios.php">Usuários</a></li>
                        <li class="breadcrumb-item active">Cadastrar</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <p class="text-muted">Preencha os dados abaixo para cadastrar um novo usuário no sistema</p>

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

            <!-- Formulário de Cadastro -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-plus mr-1"></i>
                        Dados do Novo Usuário
                    </h3>
                </div>
                <form method="POST" action="">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nome">
                                        <i class="fas fa-user mr-1"></i>Nome Completo <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="nome" name="nome"
                                           value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>"
                                           required maxlength="100">
                                    <small class="form-text text-muted">Nome completo do usuário</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="usuario">
                                        <i class="fas fa-at mr-1"></i>Usuário <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="usuario" name="usuario"
                                           value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>"
                                           required maxlength="50">
                                    <small class="form-text text-muted">Nome de usuário para login</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">
                                        <i class="fas fa-envelope mr-1"></i>Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                           required maxlength="100">
                                    <small class="form-text text-muted">Email válido para contato</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefone">
                                        <i class="fas fa-phone mr-1"></i>Telefone
                                    </label>
                                    <input type="text" class="form-control" id="telefone" name="telefone"
                                           value="<?php echo isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : ''; ?>"
                                           maxlength="20">
                                    <small class="form-text text-muted">Telefone para contato (opcional)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="senha">
                                        <i class="fas fa-lock mr-1"></i>Senha <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control" id="senha" name="senha" required minlength="6">
                                    <small class="form-text text-muted">Mínimo 6 caracteres</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="confirmar_senha">
                                        <i class="fas fa-lock mr-1"></i>Confirmar Senha <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required minlength="6">
                                    <small class="form-text text-muted">Repita a senha</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="perfil">
                                        <i class="fas fa-user-tag mr-1"></i>Perfil <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" id="perfil" name="perfil" required>
                                        <option value="">Selecione um perfil</option>
                                        <option value="analista" <?php echo (isset($_POST['perfil']) && $_POST['perfil'] === 'analista') ? 'selected' : ''; ?>>Analista</option>
                                        <option value="admin" <?php echo (isset($_POST['perfil']) && $_POST['perfil'] === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                    </select>
                                    <small class="form-text text-muted">Nível de acesso do usuário</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Cadastrar Usuário
                        </button>
                        <a href="usuarios.php<?php echo $is_iframe ? '?iframe=1' : ''; ?>" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i>Voltar
                        </a>
                    </div>
                </form>
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
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
// Validação de senha em tempo real
$(document).ready(function() {
    $('#confirmar_senha').on('keyup', function() {
        var senha = $('#senha').val();
        var confirmar = $(this).val();

        if (senha !== confirmar) {
            $(this).addClass('is-invalid');
            $('#senha-feedback').remove();
            $(this).after('<div id="senha-feedback" class="invalid-feedback">As senhas não coincidem.</div>');
        } else {
            $(this).removeClass('is-invalid');
            $(this).addClass('is-valid');
            $('#senha-feedback').remove();
        }
    });

    $('#senha').on('keyup', function() {
        var senha = $(this).val();
        var confirmar = $('#confirmar_senha').val();

        if (confirmar && senha !== confirmar) {
            $('#confirmar_senha').addClass('is-invalid');
            $('#senha-feedback').remove();
            $('#confirmar_senha').after('<div id="senha-feedback" class="invalid-feedback">As senhas não coincidem.</div>');
        } else if (confirmar) {
            $('#confirmar_senha').removeClass('is-invalid');
            $('#confirmar_senha').addClass('is-valid');
            $('#senha-feedback').remove();
        }
    });

    // Validação do formulário antes do submit
    $('form').on('submit', function(e) {
        var senha = $('#senha').val();
        var confirmar = $('#confirmar_senha').val();

        if (senha !== confirmar) {
            e.preventDefault();
            alert('As senhas não coincidem. Por favor, verifique e tente novamente.');
            return false;
        }
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
                        <i class="fas fa-user-plus mr-2"></i>
                        Cadastrar Novo Usuário
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#" onclick="window.top.loadPage('admin.php')">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#" onclick="window.top.loadPage('usuarios.php?iframe=1')">Usuários</a></li>
                        <li class="breadcrumb-item active">Cadastrar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <p class="text-muted">Preencha os dados abaixo para cadastrar um novo usuário no sistema</p>

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

            <!-- Formulário de Cadastro -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-plus mr-1"></i>
                        Dados do Novo Usuário
                    </h3>
                </div>
                <form method="POST" action="">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nome">
                                        <i class="fas fa-user mr-1"></i>Nome Completo <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="nome" name="nome"
                                           value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>"
                                           required maxlength="100">
                                    <small class="form-text text-muted">Nome completo do usuário</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="usuario">
                                        <i class="fas fa-at mr-1"></i>Usuário <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="usuario" name="usuario"
                                           value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>"
                                           required maxlength="50">
                                    <small class="form-text text-muted">Nome de usuário para login</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">
                                        <i class="fas fa-envelope mr-1"></i>Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                           required maxlength="100">
                                    <small class="form-text text-muted">Email válido para contato</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefone">
                                        <i class="fas fa-phone mr-1"></i>Telefone
                                    </label>
                                    <input type="text" class="form-control" id="telefone" name="telefone"
                                           value="<?php echo isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : ''; ?>"
                                           maxlength="20">
                                    <small class="form-text text-muted">Telefone para contato (opcional)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="senha">
                                        <i class="fas fa-lock mr-1"></i>Senha <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control" id="senha" name="senha" required minlength="6">
                                    <small class="form-text text-muted">Mínimo 6 caracteres</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="confirmar_senha">
                                        <i class="fas fa-lock mr-1"></i>Confirmar Senha <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required minlength="6">
                                    <small class="form-text text-muted">Repita a senha</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="perfil">
                                        <i class="fas fa-user-tag mr-1"></i>Perfil <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" id="perfil" name="perfil" required>
                                        <option value="">Selecione um perfil</option>
                                        <option value="analista" <?php echo (isset($_POST['perfil']) && $_POST['perfil'] === 'analista') ? 'selected' : ''; ?>>Analista</option>
                                        <option value="admin" <?php echo (isset($_POST['perfil']) && $_POST['perfil'] === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                    </select>
                                    <small class="form-text text-muted">Nível de acesso do usuário</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Cadastrar Usuário
                        </button>
                        <a href="usuarios.php<?php echo $is_iframe ? '?iframe=1' : ''; ?>" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i>Voltar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
// Validação de senha em tempo real
$(document).ready(function() {
    $('#confirmar_senha').on('keyup', function() {
        var senha = $('#senha').val();
        var confirmar = $(this).val();

        if (senha !== confirmar) {
            $(this).addClass('is-invalid');
            $('#senha-feedback').remove();
            $(this).after('<div id="senha-feedback" class="invalid-feedback">As senhas não coincidem.</div>');
        } else {
            $(this).removeClass('is-invalid');
            $(this).addClass('is-valid');
            $('#senha-feedback').remove();
        }
    });

    $('#senha').on('keyup', function() {
        var senha = $(this).val();
        var confirmar = $('#confirmar_senha').val();

        if (confirmar && senha !== confirmar) {
            $('#confirmar_senha').addClass('is-invalid');
            $('#senha-feedback').remove();
            $('#confirmar_senha').after('<div id="senha-feedback" class="invalid-feedback">As senhas não coincidem.</div>');
        } else if (confirmar) {
            $('#confirmar_senha').removeClass('is-invalid');
            $('#confirmar_senha').addClass('is-valid');
            $('#senha-feedback').remove();
        }
    });

    // Validação do formulário antes do submit
    $('form').on('submit', function(e) {
        var senha = $('#senha').val();
        var confirmar = $('#confirmar_senha').val();

        if (senha !== confirmar) {
            e.preventDefault();
            alert('As senhas não coincidem. Por favor, verifique e tente novamente.');
            return false;
        }
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
mysqli_close($conexao);
?>
