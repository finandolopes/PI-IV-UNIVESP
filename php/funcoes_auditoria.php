<?php
// Funções de auditoria
function log_auditoria($conexao, $usuario_id, $acao, $tabela = null, $registro_id = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = mysqli_prepare($conexao, "INSERT INTO logs_auditoria (usuario_id, acao, tabela_afetada, registro_id, ip_address) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issis", $usuario_id, $acao, $tabela, $registro_id, $ip);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
?>