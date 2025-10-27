<?php
include_once('php/conexao.php');

// Verificar usuários
$query = "SELECT id, usuario, senha, perfil FROM usuarios";
$result = mysqli_query($conexao, $query);

echo "Usuários no banco de dados:\n";
echo "========================\n";

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "ID: " . $row['id'] . "\n";
        echo "Usuário: " . $row['usuario'] . "\n";
        echo "Senha (hash): " . substr($row['senha'], 0, 20) . "...\n";
        echo "Perfil: " . $row['perfil'] . "\n";
        echo "---\n";
    }
} else {
    echo "Nenhum usuário encontrado!\n";
}

// Verificar estrutura da tabela
echo "\nEstrutura da tabela usuarios:\n";
echo "=============================\n";
$query_structure = "DESCRIBE usuarios";
$result_structure = mysqli_query($conexao, $query_structure);

while ($row = mysqli_fetch_assoc($result_structure)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

mysqli_close($conexao);
?>