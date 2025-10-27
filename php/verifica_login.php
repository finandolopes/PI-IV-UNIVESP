<?php
    session_start();
    if(empty($_SESSION['usuario'])){
        header('Location: ../index.php');
    }

    function verificarLoginAdmin() {
        if(empty($_SESSION['usuario'])){
            header('Location: ../index.php');
            exit();
        }

        // Aqui você pode adicionar verificação adicional para perfil admin
        // Por enquanto, apenas verifica se está logado
    }
?>