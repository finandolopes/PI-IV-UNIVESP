<?php
include_once('php/conexao.php');

// Criar tabela newsletter
$sql_newsletter = "CREATE TABLE IF NOT EXISTS newsletter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    data_inscricao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('ativo','inativo') DEFAULT 'ativo'
)";

if (mysqli_query($conexao, $sql_newsletter)) {
    echo "✅ Tabela newsletter criada com sucesso!\n";
} else {
    echo "❌ Erro ao criar tabela newsletter: " . mysqli_error($conexao) . "\n";
}

// Criar tabela reset_senha_solicitacoes
$sql_reset = "CREATE TABLE IF NOT EXISTS reset_senha_solicitacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    email VARCHAR(255) NOT NULL,
    nome_usuario VARCHAR(255),
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pendente','processado','cancelado') DEFAULT 'pendente',
    nova_senha VARCHAR(255) NULL,
    data_processamento TIMESTAMP NULL
)";

if (mysqli_query($conexao, $sql_reset)) {
    echo "✅ Tabela reset_senha_solicitacoes criada com sucesso!\n";
} else {
    echo "❌ Erro ao criar tabela reset_senha_solicitacoes: " . mysqli_error($conexao) . "\n";
}

// Inserir dados de exemplo na newsletter
$sql_insert = "INSERT IGNORE INTO newsletter (email, data_inscricao, status) VALUES
('teste@email.com', NOW(), 'ativo'),
('usuario@exemplo.com', NOW() - INTERVAL 5 DAY, 'ativo'),
('cliente@empresa.com', NOW() - INTERVAL 10 DAY, 'ativo')";

if (mysqli_query($conexao, $sql_insert)) {
    echo "✅ Dados de exemplo inseridos na tabela newsletter!\n";
} else {
    echo "❌ Erro ao inserir dados: " . mysqli_error($conexao) . "\n";
}

echo "\n🎉 Processo concluído! As tabelas foram criadas.\n";

mysqli_close($conexao);
?>