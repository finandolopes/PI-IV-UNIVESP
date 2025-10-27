<?php
    // Função para criar um novo usuário
    function criarUsuario($conexao, $nome, $usuario, $senha, $email) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conexao, "INSERT INTO usuarios (nome, usuario, senha, email, perfil, status) VALUES (?, ?, ?, ?, 'usuario', 'ativo')");
        mysqli_stmt_bind_param($stmt, "ssss", $nome, $usuario, $senhaHash, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Função para editar um usuário existente
    function editarUsuario($conexao, $id, $nome, $usuario, $senha, $email) {
        if (!empty($senha)) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conexao, "UPDATE usuarios SET nome=?, usuario=?, senha=?, email=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssssi", $nome, $usuario, $senhaHash, $email, $id);
        } else {
            $stmt = mysqli_prepare($conexao, "UPDATE usuarios SET nome=?, usuario=?, email=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sssi", $nome, $usuario, $email, $id);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Função para excluir um usuário
    function excluirUsuario($conexao, $id) {
        $stmt = mysqli_prepare($conexao, "DELETE FROM usuarios WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Função para listar todos os usuários
    function listarUsuarios($conexao) {
        $sql = "SELECT * FROM usuarios";
        $resultado = mysqli_query($conexao, $sql);
        $usuarios = array();
        while ($usuario = mysqli_fetch_assoc($resultado)) {
            $usuarios[] = $usuario;
        }
        return $usuarios;
    }
    // Função para buscar um usuário pelo ID
function buscarUsuarioPorId($conexao, $id) {
    $stmt = mysqli_prepare($conexao, "SELECT * FROM usuarios WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $usuario;
}
?>
