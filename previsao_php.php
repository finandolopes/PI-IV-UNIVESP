<?php
// Previsão de Horários de Pico em PHP - Substitui o Python
include_once('php/conexao.php');

echo "<h1>🔮 Previsão de Horários de Pico - CONFINTER</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } table { border-collapse: collapse; width: 100%; margin-bottom: 20px; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; } .previsao { background-color: #e8f5e8; } .historico { background-color: #f5f5f5; }</style>";

// Função para calcular média móvel simples
function mediaMovel($dados, $periodo = 7) {
    $resultado = [];
    for ($i = $periodo - 1; $i < count($dados); $i++) {
        $soma = 0;
        for ($j = $i - $periodo + 1; $j <= $i; $j++) {
            $soma += $dados[$j];
        }
        $resultado[] = $soma / $periodo;
    }
    return $resultado;
}

// Função para calcular tendência linear simples
function calcularTendencia($dados) {
    $n = count($dados);
    if ($n < 2) return 0;

    $somaX = 0;
    $somaY = 0;
    $somaXY = 0;
    $somaX2 = 0;

    for ($i = 0; $i < $n; $i++) {
        $somaX += $i;
        $somaY += $dados[$i];
        $somaXY += $i * $dados[$i];
        $somaX2 += $i * $i;
    }

    $denominador = $n * $somaX2 - $somaX * $somaX;
    if ($denominador == 0) return 0;

    $slope = ($n * $somaXY - $somaX * $somaY) / $denominador;
    return $slope;
}

// 1. Análise histórica de horários
echo "<h2>📊 Análise Histórica de Horários</h2>";

// Dados históricos por hora
$sql = "SELECT HOUR(data_visita) as hora, COUNT(*) as visitas
        FROM contador_visitas
        WHERE data_visita >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY HOUR(data_visita)
        ORDER BY hora";
$result = mysqli_query($conexao, $sql);

$historicoHoras = [];
while ($row = mysqli_fetch_assoc($result)) {
    $historicoHoras[$row['hora']] = $row['visitas'];
}

// Preencher horas sem dados com 0
for ($i = 0; $i < 24; $i++) {
    if (!isset($historicoHoras[$i])) {
        $historicoHoras[$i] = 0;
    }
}
ksort($historicoHoras);

echo "<h3>Visitas por Hora (Dados Históricos)</h3>";
echo "<table class='historico'>";
echo "<tr><th>Hora</th><th>Visitas Históricas</th><th>Média Diária</th></tr>";
foreach ($historicoHoras as $hora => $visitas) {
    $mediaDiaria = round($visitas / 30, 2); // Aproximadamente 30 dias
    echo "<tr><td>{$hora}h</td><td>$visitas</td><td>$mediaDiaria</td></tr>";
}
echo "</table>";

// 2. Cálculo de tendências
echo "<h2>📈 Análise de Tendências</h2>";

$dadosArray = array_values($historicoHoras);
$tendencia = calcularTendencia($dadosArray);
$mediaMovel = mediaMovel($dadosArray, 3);

echo "<p><strong>Tendência geral:</strong> ";
if ($tendencia > 0.5) {
    echo "<span style='color: green;'>Crescente 📈</span>";
} elseif ($tendencia < -0.5) {
    echo "<span style='color: red;'>Decrescente 📉</span>";
} else {
    echo "<span style='color: blue;'>Estável 📊</span>";
}
echo " (slope: " . round($tendencia, 3) . ")</p>";

// 3. Previsões para os próximos dias
echo "<h2>🔮 Previsões de Horários de Pico</h2>";

// Calcular médias por hora e aplicar fatores de ajuste
$previsoes = [];
foreach ($historicoHoras as $hora => $historico) {
    $mediaHistorica = $historico / 30; // Média diária

    // Fatores de ajuste baseados em padrões típicos
    $fatorDiaUtil = 1.0;
    $fatorHorarioComercial = 1.0;

    // Ajuste para dias úteis (seg-sex)
    if (date('N') >= 1 && date('N') <= 5) {
        $fatorDiaUtil = 1.2; // 20% a mais em dias úteis
    }

    // Ajuste para horários comerciais
    if ($hora >= 9 && $hora <= 17) {
        $fatorHorarioComercial = 1.3; // 30% a mais no horário comercial
    } elseif ($hora >= 18 && $hora <= 21) {
        $fatorHorarioComercial = 1.1; // 10% a mais no período da noite
    }

    // Aplicar tendência
    $fatorTendencia = 1 + ($tendencia * 0.1); // 10% da tendência

    // Previsão final
    $previsao = round($mediaHistorica * $fatorDiaUtil * $fatorHorarioComercial * $fatorTendencia);

    $previsoes[$hora] = [
        'historico' => $historico,
        'media_diaria' => round($mediaHistorica, 2),
        'previsao' => $previsao,
        'fator_ajuste' => round($fatorDiaUtil * $fatorHorarioComercial * $fatorTendencia, 2)
    ];
}

echo "<table class='previsao'>";
echo "<tr><th>Hora</th><th>Histórico (30 dias)</th><th>Média Diária</th><th>Fator Ajuste</th><th>Previsão Hoje</th><th>Classificação</th></tr>";
foreach ($previsoes as $hora => $dados) {
    $classificacao = '';
    if ($dados['previsao'] >= 10) {
        $classificacao = '<span style="color: red;">🚨 Pico Alto</span>';
    } elseif ($dados['previsao'] >= 5) {
        $classificacao = '<span style="color: orange;">⚠️ Pico Médio</span>';
    } else {
        $classificacao = '<span style="color: green;">✅ Normal</span>';
    }

    echo "<tr>";
    echo "<td>{$hora}h</td>";
    echo "<td>{$dados['historico']}</td>";
    echo "<td>{$dados['media_diaria']}</td>";
    echo "<td>{$dados['fator_ajuste']}</td>";
    echo "<td><strong>{$dados['previsao']}</strong></td>";
    echo "<td>$classificacao</td>";
    echo "</tr>";
}
echo "</table>";

// 4. Recomendações
echo "<h2>💡 Recomendações</h2>";

$horariosPico = array_filter($previsoes, function($dados) {
    return $dados['previsao'] >= 5;
});

if (!empty($horariosPico)) {
    echo "<h3>Horários de Pico Identificados:</h3>";
    echo "<ul>";
    foreach ($horariosPico as $hora => $dados) {
        echo "<li><strong>{$hora}h</strong>: Previsão de {$dados['previsao']} visitas - Prepare equipe adicional</li>";
    }
    echo "</ul>";
} else {
    echo "<p>✅ Nenhum horário de pico crítico identificado para hoje.</p>";
}

// Salvar previsões no banco (opcional)
$sql = "INSERT INTO previsoes_pico (data_previsao, hora_previsao, previsao_visitas, modelo_usado)
        VALUES (CURDATE(), ?, ?, 'PHP-SimpleML')";

foreach ($previsoes as $hora => $dados) {
    $stmt = $conexao->prepare($sql);
    $horaFormatada = sprintf('%02d:00:00', $hora);
    $stmt->bind_param("sis", $horaFormatada, $dados['previsao']);
    $stmt->execute();
    $stmt->close();
}

echo "<p style='color: green;'>✅ Previsões salvas no banco de dados!</p>";

echo "<br><a href='index.php'>← Voltar ao Site</a> | <a href='analise_php.php'>Ver Análise →</a> | <a href='dashboard_php.php'>Ver Dashboard →</a>";

$conexao->close();
?>
