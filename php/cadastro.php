<?php 
    include_once('../php/verifica_login.php');
    include_once('conexao.php');
        
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="../script/jquery.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/bootstrap.css">
    <script src="../script/bootstrap.js"></script>
    <title>Cadastrar Usu�rio</title>
</head>
<body class="bg-dark">
    <div class="container mt-5">
        <h1 style="color: white;">Cadastrar Usu�rio</h1>
        <form action="processa_cadastro.php" method="POST">
            <div class="mb-3">
                <label for="nome" class="form-label" style="color: white;">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="mb-3">
                <label for="usuario" class="form-label" style="color: white;">Usu�rio</label>
                <input type="text" class="form-control" id="usuario" name="usuario" required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label" style="color: white;">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label" style="color: white;">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar</button>
            <a href="admin.php" class="btn btn-secondary">Cancelar</a>
        </form>
        <!-- Listagem de usu�rios cadastrados -->
        <h1 class="mt-5">Usu�rios Cadastrados</h1>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Usu�rio</th>
                    <th>E-mail</th>
                    <th>A��es</th>
                </tr>
            </thead>
            <tbody>
                <!-- Loop para exibir os usu�rios -->
                <?php while($row = mysqli_fetch_assoc($result_usuarios)) { ?>
                    <tr>
                        <!-- Nome do usu�rio -->
                        <td><?php echo $row['nome']; ?></td>
                        <!-- Nome de usu�rio -->
                        <td><?php echo $row['usuario']; ?></td>
                        <!-- E-mail do usu�rio -->
                        <td><?php echo $row['email']; ?></td>
                        <!-- Bot�es de a��o -->
                        <td>
                            <!-- Bot�o para editar -->
                            <a href='editar.php?id=<?php echo $row['id']; ?>' class='btn btn-primary'>Editar</a>
                            <!-- Bot�o para excluir com alerta de confirma��o -->
                            <button type="button" class="btn btn-danger" onclick="confirmarExclusao(<?php echo $row['id']; ?>)">Excluir</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Bot�o para voltar -->
        <a href="admin.php" class="btn btn-secondary mt-3">Voltar</a>
    </div>

    <!-- Script para confirmar exclus�o -->
    <script>
        function confirmarExclusao(id) {
            // Exibe o alerta de confirma��o
            var confirmacao = confirm("Voc� realmente deseja excluir este usu�rio?");
            // Se confirmar, redireciona para a p�gina de exclus�o
            if (confirmacao) {
                window.location.href = "excluir.php?id=" + id;
            }
        }
    </script>
</body>
</html>