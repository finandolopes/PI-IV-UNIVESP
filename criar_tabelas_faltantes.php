<?php
include_once('conexao.php');

// Criar tabela newsletter se não existir
$sql_newsletter = "CREATE TABLE IF NOT EXISTS newsletter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    data_inscricao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('ativo','inativo') DEFAULT 'ativo'
)";

if (mysqli_query($conexao, $sql_newsletter)) {
    echo "Tabela newsletter criada com sucesso!\n";
} else {
    echo "Erro ao criar tabela newsletter: " . mysqli_error($conexao) . "\n";
}

// Criar tabela reset_senha_solicitacoes para o sistema de reset de senha
$sql_reset_solicitacoes = "CREATE TABLE IF NOT EXISTS reset_senha_solicitacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    email VARCHAR(255) NOT NULL,
    nome_usuario VARCHAR(255),
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pendente','processado','cancelado') DEFAULT 'pendente',
    nova_senha VARCHAR(255) NULL,
    data_processamento TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
)";

if (mysqli_query($conexao, $sql_reset_solicitacoes)) {
    echo "Tabela reset_senha_solicitacoes criada com sucesso!\n";
} else {
    echo "Erro ao criar tabela reset_senha_solicitacoes: " . mysqli_error($conexao) . "\n";
}

mysqli_close($conexao);
?>