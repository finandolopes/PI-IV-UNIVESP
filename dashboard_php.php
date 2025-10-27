<?php
// Dashboard em PHP puro - Substitui o dashboard Python
include_once('php/conexao.php');

// Função para obter dados de visitas
function getDadosVisitas($dias = 30) {
    global $conexao;
    $sql = "SELECT DATE(data_visita) as data, COUNT(*) as visitas
            FROM contador_visitas
            WHERE data_visita >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(data_visita)
            ORDER BY data";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $dias);
    $stmt->execute();
    return $stmt->get_result();
}

// Função para obter dados de requisições
function getDadosRequisicoes($dias = 30) {
    global $conexao;
    $sql = "SELECT DATE(data_requisicao) as data, COUNT(*) as requisicoes
            FROM requisicoes
            WHERE data_requisicao >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(data_requisicao)
            ORDER BY data";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $dias);
    $stmt->execute();
    return $stmt->get_result();
}

// Função para análise de horários de pico
function getHorariosPico($dias = 30) {
    global $conexao;
    $sql = "SELECT HOUR(data_visita) as hora, COUNT(*) as visitas
            FROM contador_visitas
            WHERE data_visita >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY HOUR(data_visita)
            ORDER BY hora";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $dias);
    $stmt->execute();
    return $stmt->get_result();
}

// Obter dados
$dadosVisitas = getDadosVisitas();
$dadosRequisicoes = getDadosRequisicoes();
$horariosPico = getHorariosPico();

// Calcular métricas
$totalVisitas = 0;
$totalRequisicoes = 0;

while ($row = $dadosVisitas->fetch_assoc()) {
    $totalVisitas += $row['visitas'];
}
$dadosVisitas->data_seek(0); // Reset pointer

while ($row = $dadosRequisicoes->fetch_assoc()) {
    $totalRequisicoes += $row['requisicoes'];
}
$dadosRequisicoes->data_seek(0); // Reset pointer

$taxaConversao = $totalVisitas > 0 ? round(($totalRequisicoes / $totalVisitas) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CONFINTER - Dashboard PHP</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix@0.2.0/dist/chartjs-chart-matrix.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .metric-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 10px;
        }
        .metric-value {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h1 class="text-center my-4">CONFINTER - Dashboard de Análise (PHP)</h1>

        <!-- Métricas -->
        <div class="row">
            <div class="col-md-4">
                <div class="metric-card">
                    <h3>Total de Visitas</h3>
                    <div class="metric-value"><?php echo $totalVisitas; ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card">
                    <h3>Total de Requisições</h3>
                    <div class="metric-value"><?php echo $totalRequisicoes; ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card">
                    <h3>Taxa de Conversão</h3>
                    <div class="metric-value"><?php echo $taxaConversao; ?>%</div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Visitas por Dia</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="visitasChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Horários de Pico</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="horariosChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Novo gráfico: Heatmap de horários por dia -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Heatmap: Visitas por Dia da Semana e Hora</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="heatmapChart" width="800" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Dados Recentes -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Dados Recentes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Data</th>
                                <th>Informação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Últimas visitas
                            $sql = "SELECT 'Visita' as tipo, data_visita as data, CONCAT('IP: ', ip_address) as info
                                    FROM contador_visitas
                                    ORDER BY data_visita DESC LIMIT 5";
                            $result = mysqli_query($conexao, $sql);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>{$row['tipo']}</td>";
                                echo "<td>{$row['data']}</td>";
                                echo "<td>{$row['info']}</td>";
                                echo "</tr>";
                            }

                            // Últimas requisições
                            $sql = "SELECT 'Requisição' as tipo, data_hora as data,
                                    CONCAT(c.nome, ' - ', r.tipo) as info
                                    FROM requisicoes r
                                    LEFT JOIN clientes c ON r.id_cliente = c.id_cliente
                                    ORDER BY data_hora DESC LIMIT 5";
                            $result = mysqli_query($conexao, $sql);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>{$row['tipo']}</td>";
                                echo "<td>{$row['data']}</td>";
                                echo "<td>{$row['info']}</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dados para os gráficos
        const dadosVisitas = {
            labels: [<?php
                $labels = [];
                while ($row = $dadosVisitas->fetch_assoc()) {
                    $labels[] = "'" . date('d/m', strtotime($row['data'])) . "'";
                }
                echo implode(',', $labels);
            ?>],
            datasets: [{
                label: 'Visitas',
                data: [<?php
                    $dadosVisitas->data_seek(0);
                    $data = [];
                    while ($row = $dadosVisitas->fetch_assoc()) {
                        $data[] = $row['visitas'];
                    }
                    echo implode(',', $data);
                ?>],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        };

        const dadosHorarios = {
            labels: [<?php
                $labels = [];
                for ($i = 0; $i < 24; $i++) {
                    $labels[] = "'{$i}h'";
                }
                echo implode(',', $labels);
            ?>],
            datasets: [{
                label: 'Visitas por Hora',
                data: [<?php
                    $data = array_fill(0, 24, 0);
                    while ($row = $horariosPico->fetch_assoc()) {
                        $data[$row['hora']] = $row['visitas'];
                    }
                    echo implode(',', $data);
                ?>],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        };

        // Criar gráficos
        new Chart(document.getElementById('visitasChart'), {
            type: 'line',
            data: dadosVisitas,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Visitas nos Últimos 30 Dias'
                    }
                }
            }
        });

        new Chart(document.getElementById('horariosChart'), {
            type: 'bar',
            data: dadosHorarios,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Padrão de Visitas por Hora'
                    }
                }
            }
        });

        // Heatmap de horários por dia da semana
        <?php
        // Obter dados para heatmap
        $sql = "SELECT DAYOFWEEK(data_visita) as dia_semana, HOUR(data_visita) as hora, COUNT(*) as visitas
                FROM contador_visitas
                WHERE data_visita >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DAYOFWEEK(data_visita), HOUR(data_visita)
                ORDER BY dia_semana, hora";
        $result = mysqli_query($conexao, $sql);

        $heatmapData = array_fill(0, 7, array_fill(0, 24, 0));
        while ($row = mysqli_fetch_assoc($result)) {
            $dia = $row['dia_semana'] - 1; // 0-6 para array
            $hora = $row['hora'];
            $heatmapData[$dia][$hora] = (int)$row['visitas'];
        }
        ?>

        const heatmapData = {
            labels: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
            datasets: [{
                label: 'Visitas',
                data: [
                    <?php
                    for ($dia = 0; $dia < 7; $dia++) {
                        for ($hora = 0; $hora < 24; $hora++) {
                            echo "{x: $hora, y: $dia, v: {$heatmapData[$dia][$hora]}},";
                        }
                    }
                    ?>
                ],
                backgroundColor: function(context) {
                    const value = context.parsed.v;
                    const alpha = Math.min(value / 10, 1); // Máximo alpha = 1
                    return `rgba(54, 162, 235, ${alpha})`;
                },
                borderWidth: 1,
                borderColor: 'rgba(54, 162, 235, 0.5)',
                width: 20,
                height: 20
            }]
        };

        new Chart(document.getElementById('heatmapChart'), {
            type: 'matrix',
            data: heatmapData,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Heatmap: Visitas por Dia da Semana e Hora'
                    },
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                const hora = context[0].parsed.x;
                                const dia = context[0].parsed.y;
                                const diasSemana = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                                return `${diasSemana[dia]} - ${hora}h`;
                            },
                            label: function(context) {
                                return `Visitas: ${context.parsed.v}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Hora do Dia'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Dia da Semana'
                        },
                        ticks: {
                            callback: function(value) {
                                const dias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
                                return dias[value];
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php $conexao->close(); ?>
