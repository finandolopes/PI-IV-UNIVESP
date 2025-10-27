<?php
include '../php/conexao.php';

echo "Estrutura da tabela depoimentos:\n";
$result = mysqli_query($conexao, 'DESCRIBE depoimentos');
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . ($row['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . ' - ' . ($row['Default'] ?? 'NULL') . "\n";
}

echo "\nEstrutura da tabela adm:\n";
$result = mysqli_query($conexao, 'DESCRIBE adm');
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . ($row['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . ' - ' . ($row['Default'] ?? 'NULL') . "\n";
}

echo "\nEstrutura da tabela usuarios:\n";
$result = mysqli_query($conexao, 'DESCRIBE usuarios');
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . ($row['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . ' - ' . ($row['Default'] ?? 'NULL') . "\n";
}

mysqli_close($conexao);
?>