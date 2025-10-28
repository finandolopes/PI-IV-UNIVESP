<?php
session_start();
include_once('php/conexao.php');

// Verificar se usuário já está logado
if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($usuario) || empty($email)) {
        $mensagem = 'Por favor, preencha todos os campos.';
        $tipo_mensagem = 'danger';
    } else {
        // Verificar se usuário existe e email corresponde
        $query = "SELECT id_usuario, nome, email FROM adm WHERE usuario = ? AND email = ?";
        $stmt = $conexao->prepare($query);
        $stmt->bind_param("ss", $usuario, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();

            // Verificar se já existe uma solicitação pendente
            $check_query = "SELECT id FROM reset_senha_solicitacoes WHERE usuario_id = ? AND status = 'pendente'";
            $check_stmt = $conexao->prepare($check_query);
            $check_stmt->bind_param("i", $user_data['id_usuario']);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $mensagem = 'Você já possui uma solicitação de reset pendente. Aguarde o processamento pelo administrador.';
                $tipo_mensagem = 'warning';
            } else {
                // Inserir solicitação de reset
                $insert_query = "INSERT INTO reset_senha_solicitacoes (usuario_id, nome_usuario, usuario, email, motivo) VALUES (?, ?, ?, ?, ?)";
                $insert_stmt = $conexao->prepare($insert_query);
                $motivo = 'Solicitação via formulário de reset de senha';
                $insert_stmt->bind_param("issss", $user_data['id_usuario'], $user_data['nome'], $usuario, $email, $motivo);

                if ($insert_stmt->execute()) {
                    $mensagem = 'Solicitação de reset de senha enviada com sucesso! O administrador será notificado e entrará em contato em breve.';
                    $tipo_mensagem = 'success';
                } else {
                    $mensagem = 'Erro ao processar solicitação. Tente novamente.';
                    $tipo_mensagem = 'danger';
                }
                $insert_stmt->close();
            }
            $check_stmt->close();
        } else {
            $mensagem = 'Usuário ou email não encontrado. Verifique os dados informados.';
            $tipo_mensagem = 'danger';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset de Senha - CONFINTER</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .reset-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }
        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .reset-header h2 {
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .reset-header p {
            color: #666;
            margin: 0;
        }
        .form-group label {
            font-weight: 500;
            color: #333;
        }
        .btn-reset {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <i class="fas fa-key fa-3x text-primary mb-3"></i>
            <h2>Reset de Senha</h2>
            <p>Solicite uma nova senha para sua conta</p>
        </div>

        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-<?php echo $tipo_mensagem === 'success' ? 'check-circle' : ($tipo_mensagem === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?> mr-2"></i>
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="usuario">
                    <i class="fas fa-user mr-1"></i>Usuário:
                </label>
                <input type="text" class="form-control" id="usuario" name="usuario" required
                       placeholder="Digite seu nome de usuário" value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope mr-1"></i>Email:
                </label>
                <input type="email" class="form-control" id="email" name="email" required
                       placeholder="Digite seu email cadastrado" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <button type="submit" class="btn btn-primary btn-reset btn-block">
                <i class="fas fa-paper-plane mr-2"></i>Solicitar Reset de Senha
            </button>
        </form>

        <div class="back-link">
            <a href="index.php">
                <i class="fas fa-arrow-left mr-1"></i>Voltar ao Site
            </a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>