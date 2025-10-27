<?php
// Incluir o arquivo de conexão
include_once('../php/conexao.php');

// Verifica se há erros na conexão
if ($conexao->connect_error) {
    die("Erro na conexão: " . $conexao->connect_error);
}

// Obter dados da visita
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$pagina = $_SERVER['REQUEST_URI'];
$sessao_id = session_id();

// Iniciar sessão se não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$sessao_id = session_id();

// Insere uma nova visita no banco de dados com mais detalhes
$sql = "INSERT INTO contador_visitas (ip_address, user_agent, pagina, sessao_id) VALUES (?, ?, ?, ?)";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("ssss", $ip_address, $user_agent, $pagina, $sessao_id);
$stmt->execute();
$stmt->close();

// Fecha a conexão
$conexao->close();
?>
