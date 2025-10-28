<?php
// Script para executar o schema.sql automaticamente
// Arquivo: executar_schema.php

echo "<h1>Execução Automática do Schema - CONFINTER</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";

require_once 'php/conexao.php';

if (!$conexao) {
    die("<p class='error'>❌ Falha na conexão com o banco de dados</p>");
}

echo "<p class='success'>✅ Conectado ao banco de dados</p>";

// Ler o arquivo schema.sql
$schema_file = 'schema.sql';
if (!file_exists($schema_file)) {
    die("<p class='error'>❌ Arquivo schema.sql não encontrado</p>");
}

echo "<p>📄 Lendo arquivo schema.sql...</p>";

// Ler o conteúdo do arquivo
$sql_content = file_get_contents($schema_file);

// Dividir o SQL em statements individuais (por ponto e vírgula)
$statements = array_filter(array_map('trim', explode(';', $sql_content)));

$success_count = 0;
$error_count = 0;
$errors = [];

echo "<p>⚡ Executando statements SQL...</p>";
echo "<pre>";

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue; // Pular comentários e statements vazios
    }

    // Remover comentários de linha
    $statement = preg_replace('/--.*$/m', '', $statement);

    if (!empty($statement)) {
        if (mysqli_query($conexao, $statement)) {
            echo "✅ Statement executado com sucesso\n";
            $success_count++;
        } else {
            $error = mysqli_error($conexao);
            echo "❌ Erro: $error\n";
            $errors[] = $error;
            $error_count++;
        }
    }
}

echo "</pre>";

echo "<hr>";
echo "<h3>📊 Resultado da Execução:</h3>";
echo "<p class='success'>✅ Statements executados com sucesso: <strong>$success_count</strong></p>";

if ($error_count > 0) {
    echo "<p class='error'>❌ Erros encontrados: <strong>$error_count</strong></p>";
    echo "<details><summary>Ver erros detalhados</summary><pre>";
    foreach ($errors as $error) {
        echo htmlspecialchars($error) . "\n";
    }
    echo "</pre></details>";
} else {
    echo "<p class='success'>🎉 Schema executado completamente sem erros!</p>";
}

// Verificar se as tabelas foram criadas
echo "<h3>🔍 Verificação das Tabelas:</h3>";
$result = mysqli_query($conexao, "SHOW TABLES");
$tables = [];
while ($row = mysqli_fetch_array($result)) {
    $tables[] = $row[0];
}

$tabelas_esperadas = [
    'usuarios', 'adm', 'clientes', 'contador_visitas', 'depoimentos',
    'empresa', 'enderecos', 'imagens_carrossel', 'slider_imagens',
    'requisicoes', 'tempo_visita', 'reset_senha', 'reset_senha_solicitacoes',
    'previsoes_pico', 'logs_sistema', 'logs_auditoria', 'logs',
    'configuracoes', 'configuracoes_sistema', 'notificacoes', 'newsletter'
];

echo "<ul>";
foreach ($tabelas_esperadas as $tabela) {
    if (in_array($tabela, $tables)) {
        echo "<li class='success'>✅ $tabela</li>";
    } else {
        echo "<li class='error'>❌ $tabela (não encontrada)</li>";
    }
}
echo "</ul>";

// Fechar conexão
mysqli_close($conexao);

echo "<hr>";
echo "<p><strong>📝 Próximos passos:</strong></p>";
echo "<ol>";
echo "<li>Teste o acesso ao painel admin: <code>admin/index.php</code></li>";
echo "<li>Verifique as configurações em <code>admin/configuracoes.php</code></li>";
echo "<li>Execute o script de atualização: <code>atualizar_bd.php</code></li>";
echo "</ol>";
?>