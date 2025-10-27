<?php
// processa_reset_senha.php - Processa solicitações de reset de senha
session_start();
include_once('conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conexao, $_POST['email']);

    // Verificar se o email existe na tabela usuarios
    $query = "SELECT id, nome, usuario FROM usuarios WHERE email = '$email'";
    $result = mysqli_query($conexao, $query);

    if (mysqli_num_rows($result) > 0) {
        $usuario = mysqli_fetch_assoc($result);

        // Inserir solicitação na tabela reset_senha_solicitacoes
        $insert_query = "INSERT INTO reset_senha_solicitacoes (usuario_id, email, nome_usuario, status)
                        VALUES ('{$usuario['id']}', '$email', '{$usuario['nome']}', 'pendente')";

        if (mysqli_query($conexao, $insert_query)) {
            $_SESSION['reset_success'] = "Solicitação de reset de senha encaminhada para o administrador! Você será notificado quando for processada.";
            header('Location: ../index.php?reset=success');
            exit();
        } else {
            $_SESSION['reset_error'] = "Erro ao processar solicitação. Tente novamente.";
            header('Location: ../index.php?reset=error');
            exit();
        }
    } else {
        $_SESSION['reset_error'] = "Email não encontrado em nossa base de dados.";
        header('Location: ../index.php?reset=error');
        exit();
    }
} else {
    header('Location: ../index.php');
    exit();
}
?>
