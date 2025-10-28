<?php
session_start();
$_SESSION['username'] = 'admin';
$_GET['iframe'] = 'true';

ob_start();
include 'admin/reset_senha_admin.php';
$content = ob_get_clean();

echo "=== DEBUG OUTPUT ===\n";
echo $content;
echo "\n=== END DEBUG ===\n";
?>