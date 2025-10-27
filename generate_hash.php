<?php
$senha = '0m3g4r3d';
$hash = password_hash($senha, PASSWORD_DEFAULT);

echo "Senha: $senha\n";
echo "Hash gerado: $hash\n";
echo "Verificação: " . (password_verify($senha, $hash) ? 'OK' : 'ERRO') . "\n";
?>