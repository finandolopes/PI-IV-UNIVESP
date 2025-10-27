<?php
include_once('php/conexao.php');

// Atualizar senha do usuário fernando.silva
$novo_hash = '$2y$10$1bWKR6Zyb72Jo8LNkhwTj.4HPefZu5WnfQr0hdNY9MACObx75njHa';

$query = "UPDATE usuarios SET senha = ? WHERE usuario = ?";
$stmt = mysqli_prepare($conexao, $query);
mysqli_stmt_bind_param($stmt, "ss", $novo_hash, $usuario);
$usuario = 'fernando.silva';

if (mysqli_stmt_execute($stmt)) {
    echo "✅ Senha do usuário fernando.silva atualizada com sucesso!\n";
    echo "Agora você pode fazer login com:\n";
    echo "Usuário: fernando.silva\n";
    echo "Senha: 0m3g4r3d\n";
} else {
    echo "❌ Erro ao atualizar senha: " . mysqli_error($conexao) . "\n";
}

mysqli_stmt_close($stmt);
mysqli_close($conexao);
?>