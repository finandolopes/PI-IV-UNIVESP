<?php
// Script para executar as atualizações do banco de dados
include_once('php/conexao.php');

// Ler o arquivo SQL
$sql = file_get_contents('sql/correcoes_schema.sql');

// Dividir em statements individuais
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $statement) {
    if (!empty($statement)) {
        if ($conexao->query($statement) === TRUE) {
            echo "Statement executado com sucesso: " . substr($statement, 0, 50) . "...\n";
        } else {
            echo "Erro ao executar statement: " . $conexao->error . "\n";
        }
    }
}

echo "Atualização do banco de dados concluída!\n";

$conexao->close();
?>
