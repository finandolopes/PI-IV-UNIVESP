<?php
include_once('php/conexao.php');

try {
    // Ler o arquivo SQL
    $sql = file_get_contents('admin/create_config_table.sql');

    // Executar o SQL
    if ($conexao->multi_query($sql)) {
        echo "Tabela 'configuracoes' criada com sucesso!\n";
        echo "Configurações padrão inseridas.\n";
    } else {
        echo "Erro ao criar tabela: " . $conexao->error . "\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>