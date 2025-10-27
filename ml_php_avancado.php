<?php
// Exemplo de como usar PHP-ML para previsões mais avançadas (opcional)
// Para instalar: composer require php-ai/php-ml

/*
Este arquivo mostra como seria uma implementação mais avançada usando PHP-ML
Para usar, primeiro instale o Composer e execute:
composer require php-ai/php-ml

Depois descomente o código abaixo.
*/

/*
// include_once('vendor/autoload.php'); // Para PHP-ML

use Phpml\Regression\LeastSquares;
use Phpml\Regression\SVR;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\Metric\Regression;

include_once('php/conexao.php');

echo "<h1>🔬 Machine Learning Avançado com PHP-ML</h1>";

// 1. Preparar dados de treinamento
$sql = "SELECT
    HOUR(data_visita) as hora,
    DAYOFWEEK(data_visita) as dia_semana,
    MONTH(data_visita) as mes,
    COUNT(*) as visitas
FROM contador_visitas
WHERE data_visita >= DATE_SUB(NOW(), INTERVAL 90 DAY)
GROUP BY DATE(data_visita), HOUR(data_visita)
ORDER BY data_visita";

$result = mysqli_query($conexao, $sql);

$samples = [];
$targets = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Features: hora, dia da semana, mês
    $samples[] = [
        (float)$row['hora'],
        (float)$row['dia_semana'],
        (float)$row['mes']
    ];
    $targets[] = (float)$row['visitas'];
}

// 2. Treinar modelo
$regression = new LeastSquares();
$regression->train($samples, $targets);

// 3. Fazer previsões para hoje
echo "<h2>Previsões para Hoje usando PHP-ML</h2>";
echo "<table border='1'>";
echo "<tr><th>Hora</th><th>Previsão (PHP-ML)</th><th>Previsão (Simples)</th></tr>";

for ($hora = 0; $hora < 24; $hora++) {
    $diaSemana = date('N'); // 1-7 (segunda-domingo)
    $mes = date('n');

    // Previsão com PHP-ML
    $previsaoML = $regression->predict([$hora, $diaSemana, $mes]);
    $previsaoML = max(0, round($previsaoML)); // Não permitir valores negativos

    // Previsão simples (média histórica)
    $sqlMedia = "SELECT AVG(visitas) as media
                 FROM (SELECT COUNT(*) as visitas
                       FROM contador_visitas
                       WHERE HOUR(data_visita) = $hora
                       AND data_visita >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                       GROUP BY DATE(data_visita)) t";
    $resultMedia = mysqli_query($conexao, $sqlMedia);
    $rowMedia = mysqli_fetch_assoc($resultMedia);
    $previsaoSimples = round($rowMedia['media'] ?? 0);

    echo "<tr>";
    echo "<td>{$hora}h</td>";
    echo "<td><strong>$previsaoML</strong></td>";
    echo "<td>$previsaoSimples</td>";
    echo "</tr>";
}

echo "</table>";

// 4. Avaliação do modelo (usando dados de teste)
echo "<h2>Avaliação do Modelo</h2>";

if (count($samples) > 10) {
    // Dividir dados em treino e teste
    $split = new StratifiedRandomSplit($samples, $targets, 0.2);
    $trainSamples = $split->getTrainSamples();
    $trainTargets = $split->getTrainLabels();
    $testSamples = $split->getTestSamples();
    $testTargets = $split->getTestLabels();

    // Treinar com dados de treino
    $model = new LeastSquares();
    $model->train($trainSamples, $trainTargets);

    // Fazer previsões nos dados de teste
    $predictions = [];
    foreach ($testSamples as $sample) {
        $predictions[] = $model->predict($sample);
    }

    // Calcular métricas
    $mse = Regression::meanSquaredError($testTargets, $predictions);
    $mae = Regression::meanAbsoluteError($testTargets, $predictions);

    echo "<p><strong>Mean Squared Error (MSE):</strong> " . round($mse, 2) . "</p>";
    echo "<p><strong>Mean Absolute Error (MAE):</strong> " . round($mae, 2) . "</p>";
} else {
    echo "<p>Dados insuficientes para avaliação do modelo.</p>";
}

$conexao->close();
*/

echo "<h1>🚀 Sistema Completo em PHP/JavaScript</h1>";
echo "<p>Este arquivo contém exemplos de como implementar ML avançado com PHP-ML.</p>";
echo "<p>Para usar o sistema completo em PHP/JavaScript, acesse:</p>";
echo "<ul>";
echo "<li><a href='analise_php.php'>📊 Análise Exploratória</a></li>";
echo "<li><a href='previsao_php.php'>🔮 Previsão de Picos</a></li>";
echo "<li><a href='dashboard_php.php'>📈 Dashboard Interativo</a></li>";
echo "</ul>";
echo "<br><a href='index.php'>← Voltar ao Site</a>";
?>
