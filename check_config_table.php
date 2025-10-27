<?php
include_once('php/conexao.php');

$query = 'SHOW TABLES LIKE "configuracoes"';
$result = $conexao->query($query);
if ($result->num_rows > 0) {
    echo 'Tabela configuracoes existe';
} else {
    echo 'Tabela configuracoes NAO existe';
}
?>