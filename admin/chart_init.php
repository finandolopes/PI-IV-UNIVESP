// Função para inicializar os gráficos
function initializeCharts() {
    // Gráfico de Pipeline de Vendas (Doughnut)
    const pipelineCtx = document.getElementById('pipelineChart').getContext('2d');
    new Chart(pipelineCtx, {
        type: 'doughnut',
        data: {
            labels: ['Aprovadas', 'Pendentes', 'Rejeitadas'],
            datasets: [{
                data: [<?php echo $approved_requests; ?>, <?php echo $pending_requests; ?>, <?php echo $rejected_requests; ?>],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Vendas por Semana (Line)
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'],
            datasets: [{
                label: 'Vendas',
                data: [<?php echo $sales_week1; ?>, <?php echo $sales_week2; ?>, <?php echo $sales_week3; ?>, <?php echo $sales_week4; ?>],
                borderColor: 'rgba(0, 123, 255, 1)',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Distribuição por Tipo de Crédito (Doughnut)
    const creditCtx = document.getElementById('creditTypeChart').getContext('2d');
    new Chart(creditCtx, {
        type: 'doughnut',
        data: {
            labels: ['Crédito Pessoal', 'Crédito Empresarial', 'Crédito Consignado'],
            datasets: [{
                data: [<?php echo $personal_credit; ?>, <?php echo $business_credit; ?>, <?php echo $consigned_credit; ?>],
                backgroundColor: [
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(108, 117, 125, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderColor: [
                    'rgba(23, 162, 184, 1)',
                    'rgba(108, 117, 125, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Conversões do Site (Line)
    const conversionCtx = document.getElementById('conversionChart').getContext('2d');
    new Chart(conversionCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
            datasets: [{
                label: 'Taxa de Conversão (%)',
                data: [<?php echo $conversion_jan; ?>, <?php echo $conversion_feb; ?>, <?php echo $conversion_mar; ?>, <?php echo $conversion_apr; ?>, <?php echo $conversion_may; ?>, <?php echo $conversion_jun; ?>],
                borderColor: 'rgba(40, 167, 69, 1)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
}