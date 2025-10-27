<?php
include '../php/conexao.php';

echo "Adicionando colunas faltantes nas tabelas...\n\n";

// Adicionar coluna status na tabela depoimentos
$sql1 = "ALTER TABLE depoimentos ADD COLUMN status ENUM('pendente', 'aprovado', 'reprovado') DEFAULT 'pendente' AFTER data_envio";
if (mysqli_query($conexao, $sql1)) {
    echo "✓ Coluna 'status' adicionada na tabela depoimentos\n";
} else {
    echo "✗ Erro ao adicionar coluna 'status' na tabela depoimentos: " . mysqli_error($conexao) . "\n";
}

// Adicionar coluna avatar na tabela adm
$sql2 = "ALTER TABLE adm ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER perfil";
if (mysqli_query($conexao, $sql2)) {
    echo "✓ Coluna 'avatar' adicionada na tabela adm\n";
} else {
    echo "✗ Erro ao adicionar coluna 'avatar' na tabela adm: " . mysqli_error($conexao) . "\n";
}

// Adicionar coluna status na tabela usuarios
$sql3 = "ALTER TABLE usuarios ADD COLUMN status ENUM('ativo', 'inativo') DEFAULT 'ativo' AFTER perfil";
if (mysqli_query($conexao, $sql3)) {
    echo "✓ Coluna 'status' adicionada na tabela usuarios\n";
} else {
    echo "✗ Erro ao adicionar coluna 'status' na tabela usuarios: " . mysqli_error($conexao) . "\n";
}

echo "\nVerificando estruturas das tabelas:\n\n";

echo "Estrutura da tabela depoimentos:\n";
$result = mysqli_query($conexao, 'DESCRIBE depoimentos');
while($row = mysqli_fetch_assoc($result)) {
    echo "- " . $row['Field'] . ' (' . $row['Type'] . ")\n";
}

echo "\nEstrutura da tabela adm:\n";
$result = mysqli_query($conexao, 'DESCRIBE adm');
while($row = mysqli_fetch_assoc($result)) {
    echo "- " . $row['Field'] . ' (' . $row['Type'] . ")\n";
}

echo "\nEstrutura da tabela usuarios:\n";
$result = mysqli_query($conexao, 'DESCRIBE usuarios');
while($row = mysqli_fetch_assoc($result)) {
    echo "- " . $row['Field'] . ' (' . $row['Type'] . ")\n";
}

mysqli_close($conexao);
echo "\nConcluído!\n";
?>