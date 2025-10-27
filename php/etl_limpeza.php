<?php
// Script ETL para limpeza e normalização dos dados
include_once('conexao.php');

// Função para limpar emails inválidos
function limparEmailsInvalidos($conexao) {
    $sql = "UPDATE clientes SET email = NULL WHERE email NOT REGEXP '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'";
    $conexao->query($sql);
    echo "Emails inválidos limpos.\n";
}

// Função para normalizar telefones
function normalizarTelefones($conexao) {
    $sql = "UPDATE clientes SET telefone = REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), ' ', '') WHERE telefone IS NOT NULL";
    $conexao->query($sql);
    echo "Telefones normalizados.\n";
}

// Função para remover duplicatas de visitas do mesmo IP na mesma hora
function removerDuplicatasVisitas($conexao) {
    $sql = "DELETE t1 FROM contador_visitas t1
            INNER JOIN contador_visitas t2
            WHERE t1.id > t2.id
            AND t1.ip_address = t2.ip_address
            AND TIMESTAMPDIFF(MINUTE, t1.data_visita, t2.data_visita) < 5";
    $conexao->query($sql);
    echo "Duplicatas de visitas removidas.\n";
}

// Executar as funções de limpeza
limparEmailsInvalidos($conexao);
normalizarTelefones($conexao);
removerDuplicatasVisitas($conexao);

echo "ETL concluído com sucesso.\n";

$conexao->close();
?>
