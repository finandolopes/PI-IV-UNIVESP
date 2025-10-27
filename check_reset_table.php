<?php
include_once('php/conexao.php');

$query = 'SHOW TABLES LIKE "reset_senha_solicitacoes"';
$result = $conexao->query($query);
if ($result->num_rows > 0) {
    echo 'Tabela reset_senha_solicitacoes existe';
} else {
    echo 'Tabela reset_senha_solicitacoes NAO existe';
}
?>