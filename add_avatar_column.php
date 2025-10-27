<?php
include_once('php/conexao.php');
$query = "ALTER TABLE adm ADD COLUMN avatar VARCHAR(255) DEFAULT NULL";
if (mysqli_query($conexao, $query)) {
    echo "Coluna avatar adicionada com sucesso!";
} else {
    echo "Erro ao adicionar coluna: " . mysqli_error($conexao);
}
$conexao->close();
?>