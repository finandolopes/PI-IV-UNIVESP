<?php
// processa_reset_senha.php - Processa solicitações de reset de senha
session_start();
include_once('conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $_SESSION['reset_error'] = 'Por favor, digite seu email.';
        header('Location: ../index.php');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['reset_error'] = 'Por favor, digite um email válido.';
        header('Location: ../index.php');
        exit();
    }

    // Verificar se o email existe na tabela adm
    $query = "SELECT id_usuario, nome, usuario FROM adm WHERE email = ?";
    $stmt = $conexao->prepare($query);
    $stmt->bind_param("s", $email);
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
            $_SESSION['reset_error'] = 'Você já possui uma solicitação de reset pendente. Aguarde o processamento pelo administrador.';
        } else {
            // Inserir solicitação de reset
            $insert_query = "INSERT INTO reset_senha_solicitacoes (usuario_id, nome_usuario, usuario, email, motivo) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conexao->prepare($insert_query);
            $motivo = 'Solicitação via modal do site - esqueceu senha';
            $insert_stmt->bind_param("issss", $user_data['id_usuario'], $user_data['nome'], $user_data['usuario'], $email, $motivo);

            if ($insert_stmt->execute()) {
                $_SESSION['reset_success'] = 'Solicitação de reset de senha enviada com sucesso! O administrador será notificado e entrará em contato em breve.';
            } else {
                $_SESSION['reset_error'] = 'Erro ao processar solicitação. Tente novamente.';
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } else {
        $_SESSION['reset_error'] = 'Email não encontrado em nossa base de dados.';
    }

    $stmt->close();
    $conexao->close();
}

header('Location: ../index.php');
exit();
?>
