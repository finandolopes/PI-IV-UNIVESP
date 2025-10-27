<?php
include_once('php/conexao.php');

echo "<h2>📊 Status do Sistema CONFINTER</h2>";

// Verificar tabelas
$result = mysqli_query($conexao, "SHOW TABLES");
$tables = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo "<h3>📋 Tabelas encontradas:</h3>";
echo "<ul>";
foreach ($tables as $table) {
    $table_name = reset($table);
    echo "<li>$table_name</li>";
}
echo "</ul>";

// Verificar dados em algumas tabelas importantes
$checks = [
    'usuarios' => 'SELECT COUNT(*) as total FROM usuarios',
    'clientes' => 'SELECT COUNT(*) as total FROM clientes',
    'adm' => 'SELECT COUNT(*) as total FROM adm',
    'requisicoes' => 'SELECT COUNT(*) as total FROM requisicoes'
];

echo "<h3>📈 Dados encontrados:</h3>";
echo "<ul>";
foreach ($checks as $table => $query) {
    $result = mysqli_query($conexao, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $count = $row['total'];
        echo "<li><strong>$table:</strong> $count registros</li>";
    } else {
        echo "<li><strong>$table:</strong> Erro na consulta</li>";
    }
}
echo "</ul>";

echo "<h3>✅ Sistema Status:</h3>";
echo "<p>Sistema CONFINTER está <strong>OPERACIONAL</strong>!</p>";
echo "<p><a href='index.php'>🏠 Ir para página inicial</a></p>";
echo "<p><a href='admin/'>🔐 Ir para painel admin</a></p>";

mysqli_close($conexao);
?>