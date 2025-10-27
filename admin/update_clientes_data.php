<?php
include_once('../php/conexao.php');

// Atualizar dados existentes para preencher as novas colunas
$sql_update = "UPDATE clientes SET
    razao_social = COALESCE(razao_social, nome),
    tipo = COALESCE(tipo, 'pf'),
    cnpj = COALESCE(cnpj, cpf)
WHERE razao_social IS NULL OR tipo IS NULL";

if (mysqli_query($conexao, $sql_update)) {
    echo "✅ Dados existentes atualizados com sucesso!\n";
    echo "Registros afetados: " . mysqli_affected_rows($conexao) . "\n";
} else {
    echo "❌ Erro ao atualizar dados: " . mysqli_error($conexao) . "\n";
}

mysqli_close($conexao);
?>