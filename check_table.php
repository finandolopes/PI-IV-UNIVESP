<?php
include 'php/conexao.php';
$result = mysqli_query($conexao, 'DESCRIBE depoimentos');
echo "Estrutura da tabela depoimentos:\n";
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}
?>