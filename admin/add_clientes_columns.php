<?php
include_once('../php/conexao.php');

// Verificar quais colunas já existem
$existing_columns = [];
$result = mysqli_query($conexao, "DESCRIBE clientes");
while($row = mysqli_fetch_assoc($result)) {
    $existing_columns[] = $row['Field'];
}

// Adicionar colunas faltantes
$columns_to_add = [
    'razao_social' => "ALTER TABLE clientes ADD COLUMN razao_social VARCHAR(255) DEFAULT NULL AFTER nome",
    'cnpj' => "ALTER TABLE clientes ADD COLUMN cnpj VARCHAR(18) DEFAULT NULL AFTER cpf",
    'tipo' => "ALTER TABLE clientes ADD COLUMN tipo ENUM('pj', 'pf') DEFAULT 'pf' AFTER cnpj",
    'responsavel' => "ALTER TABLE clientes ADD COLUMN responsavel VARCHAR(255) DEFAULT NULL AFTER tipo"
];

foreach ($columns_to_add as $column => $sql) {
    if (!in_array($column, $existing_columns)) {
        if (mysqli_query($conexao, $sql)) {
            echo "✅ Coluna '$column' adicionada com sucesso!\n";
        } else {
            echo "❌ Erro ao adicionar coluna '$column': " . mysqli_error($conexao) . "\n";
        }
    } else {
        echo "ℹ️ Coluna '$column' já existe.\n";
    }
}

// Verificar estrutura da tabela
echo "\nEstrutura da tabela clientes após as alterações:\n";
$result = mysqli_query($conexao, 'DESCRIBE clientes');
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . ($row['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . ' - ' . ($row['Default'] ?? 'NULL') . "\n";
}

mysqli_close($conexao);
?>