<?php
// Análise Exploratória em PHP - Substitui o Python
include_once('php/conexao.php');

echo "<h1>Análise Exploratória de Dados - CONFINTER</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } table { border-collapse: collapse; width: 100%; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; }</style>";

// 1. Análise de Visitas
echo "<h2>📊 Análise de Visitas</h2>";

// Total de visitas
$sql = "SELECT COUNT(*) as total FROM contador_visitas";
$result = mysqli_query($conexao, $sql);
$row = mysqli_fetch_assoc($result);
echo "<p><strong>Total de visitas registradas:</strong> " . $row['total'] . "</p>";

// Visitas por dia (últimos 30 dias)
$sql = "SELECT DATE(data_visita) as data, COUNT(*) as visitas
        FROM contador_visitas
        WHERE data_visita >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(data_visita)
        ORDER BY data";
$result = mysqli_query($conexao, $sql);

echo "<h3>Visitas por Dia (Últimos 30 dias)</h3>";
echo "<table>";
echo "<tr><th>Data</th><th>Visitas</th></tr>";
$totalVisitas = 0;
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>{$row['data']}</td><td>{$row['visitas']}</td></tr>";
    $totalVisitas += $row['visitas'];
}
echo "</table>";
echo "<p><strong>Total no período:</strong> $totalVisitas visitas</p>";

// Visitas por hora
$sql = "SELECT HOUR(data_visita) as hora, COUNT(*) as visitas
        FROM contador_visitas
        WHERE data_visita >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY HOUR(data_visita)
        ORDER BY hora";
$result = mysqli_query($conexao, $sql);

echo "<h3>Visitas por Hora do Dia</h3>";
echo "<table>";
echo "<tr><th>Hora</th><th>Visitas</th><th>Porcentagem</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    $porcentagem = $totalVisitas > 0 ? round(($row['visitas'] / $totalVisitas) * 100, 2) : 0;
    echo "<tr><td>{$row['hora']}h</td><td>{$row['visitas']}</td><td>{$porcentagem}%</td></tr>";
}
echo "</table>";

// 2. Análise de Requisições
echo "<h2>📋 Análise de Requisições</h2>";

// Total de requisições
$sql = "SELECT COUNT(*) as total FROM requisicoes";
$result = mysqli_query($conexao, $sql);
$row = mysqli_fetch_assoc($result);
$totalRequisicoes = $row['total'];
echo "<p><strong>Total de requisições:</strong> $totalRequisicoes</p>";

// Requisições por categoria
$sql = "SELECT categoria, COUNT(*) as total
        FROM requisicoes
        WHERE categoria IS NOT NULL AND categoria != ''
        GROUP BY categoria
        ORDER BY total DESC";
$result = mysqli_query($conexao, $sql);

echo "<h3>Requisições por Categoria</h3>";
echo "<table>";
echo "<tr><th>Categoria</th><th>Total</th><th>Porcentagem</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    $porcentagem = $totalRequisicoes > 0 ? round(($row['total'] / $totalRequisicoes) * 100, 2) : 0;
    echo "<tr><td>{$row['categoria']}</td><td>{$row['total']}</td><td>{$porcentagem}%</td></tr>";
}
echo "</table>";

// Horários preferidos para contato
$sql = "SELECT HOUR(horario_contato) as hora, COUNT(*) as total
        FROM requisicoes
        WHERE horario_contato IS NOT NULL
        GROUP BY HOUR(horario_contato)
        ORDER BY hora";
$result = mysqli_query($conexao, $sql);

echo "<h3>Horários Preferidos para Contato</h3>";
echo "<table>";
echo "<tr><th>Hora</th><th>Requisições</th><th>Porcentagem</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    $porcentagem = $totalRequisicoes > 0 ? round(($row['total'] / $totalRequisicoes) * 100, 2) : 0;
    echo "<tr><td>{$row['hora']}h</td><td>{$row['total']}</td><td>{$porcentagem}%</td></tr>";
}
echo "</table>";

// 3. Métricas de Conversão
echo "<h2>📈 Métricas de Conversão</h2>";

$taxaConversao = $totalVisitas > 0 ? round(($totalRequisicoes / $totalVisitas) * 100, 2) : 0;
echo "<p><strong>Taxa de Conversão:</strong> $taxaConversao% ($totalRequisicoes requisições de $totalVisitas visitas)</p>";

// Top páginas visitadas
$sql = "SELECT pagina, COUNT(*) as visitas
        FROM contador_visitas
        WHERE pagina IS NOT NULL
        GROUP BY pagina
        ORDER BY visitas DESC
        LIMIT 10";
$result = mysqli_query($conexao, $sql);

echo "<h3>Top 10 Páginas Mais Visitadas</h3>";
echo "<table>";
echo "<tr><th>Página</th><th>Visitas</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>{$row['pagina']}</td><td>{$row['visitas']}</td></tr>";
}
echo "</table>";

// 4. Análise Temporal
echo "<h2>📅 Análise Temporal</h2>";

// Visitas por dia da semana
$sql = "SELECT DAYOFWEEK(data_visita) as dia_semana, COUNT(*) as visitas
        FROM contador_visitas
        WHERE data_visita >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DAYOFWEEK(data_visita)
        ORDER BY dia_semana";
$result = mysqli_query($conexao, $sql);

$dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
echo "<h3>Visitas por Dia da Semana</h3>";
echo "<table>";
echo "<tr><th>Dia</th><th>Visitas</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    $diaNome = $dias[$row['dia_semana'] - 1];
    echo "<tr><td>$diaNome</td><td>{$row['visitas']}</td></tr>";
}
echo "</table>";

// 5. IPs mais frequentes (top 10)
$sql = "SELECT ip_address, COUNT(*) as visitas
        FROM contador_visitas
        GROUP BY ip_address
        ORDER BY visitas DESC
        LIMIT 10";
$result = mysqli_query($conexao, $sql);

echo "<h3>Top 10 IPs com Mais Visitas</h3>";
echo "<table>";
echo "<tr><th>IP Address</th><th>Visitas</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>{$row['ip_address']}</td><td>{$row['visitas']}</td></tr>";
}
echo "</table>";

echo "<br><a href='index.php'>← Voltar ao Site</a> | <a href='dashboard_php.php'>Ver Dashboard →</a>";

$conexao->close();
?>
