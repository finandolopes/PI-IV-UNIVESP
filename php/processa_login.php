<?php
session_start();

// Inclua o arquivo de conexão
include_once('conexao.php');

// Verifique se o formulário de login foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifique se os campos de usuário e senha foram preenchidos
    if(isset($_POST['usuario']) && isset($_POST['senha'])){
        $user = trim($_POST['usuario']);
        $senha = $_POST['senha'];

        // Use prepared statement para evitar SQL injection
        $stmt = mysqli_prepare($conexao, "SELECT id_usuario, usuario, senha, perfil FROM adm WHERE usuario = ?");
        mysqli_stmt_bind_param($stmt, "s", $user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // Verifique se a consulta retornou algum resultado
        if(mysqli_num_rows($result) == 1){
            $row = mysqli_fetch_assoc($result);
            $hashed_password = $row['senha'];
            $usuario_id = $row['id_usuario'];
            
            // Verifica se a senha está hasheada (MD5 ou password_hash) ou plain
            if (md5($senha) === $hashed_password || password_verify($senha, $hashed_password) || $senha === $hashed_password) {
                // Login bem-sucedido
                mysqli_stmt_close($stmt);
                $_SESSION['username'] = $user;
                $_SESSION['usuario'] = $user;
                // IDs de usuário (compatibilidade)
                $_SESSION['usuario_id'] = $usuario_id;
                $_SESSION['user_id'] = $usuario_id; // usado pelo admin.php
                // Perfil/nivel (compatibilidade)
                $_SESSION['perfil'] = $row['perfil'] ?? 'admin';
                $_SESSION['nivel'] = $row['perfil'] ?? 'admin'; // legado
                header('Location: ../admin/admin.php');
                exit();
            } else {
                // Credenciais inválidas
                mysqli_stmt_close($stmt);
                header('Location: ../index.php?error=1');
                exit();
            }
        } else {
            // Usuário não encontrado
            mysqli_stmt_close($stmt);
            header('Location: ../index.php?error=1');
            exit();
        }
    }
}
?>
