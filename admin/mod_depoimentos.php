<?php
session_start();
include_once(__DIR__ . '/../php/conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Detectar se está em iframe
$is_iframe = isset($_GET['iframe']) && ($_GET['iframe'] == '1' || $_GET['iframe'] == 'true');

// Buscar depoimentos
$query = "
 SELECT
 d.id as id_depoimento,
 d.nome_cliente,
 d.mensagem as depoimento,
 d.data_envio,
 d.status_mod as status,
 d.avaliacao,
 d.nome as cliente_email
 FROM depoimentos d
 ORDER BY d.data_envio DESC
";
$result = $conexao->query($query);
$depoimentos = $result->fetch_all(MYSQLI_ASSOC);

// Estatísticas dos depoimentos
$query_stats = "
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status_mod = 'aprovado' THEN 1 ELSE 0 END) as aprovados,
        SUM(CASE WHEN status_mod = 'pendente' THEN 1 ELSE 0 END) as pendentes,
        SUM(CASE WHEN status_mod = 'reprovado' THEN 1 ELSE 0 END) as reprovados
    FROM depoimentos
";

$result_stats = $conexao->query($query_stats);
$stats = $result_stats->fetch_assoc();

if (!$is_iframe) {
    // Versão completa com navbar e sidebar
    include 'navbar.php';
    include 'sidebar.php';
?>
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3><?php echo $stats['total'] ?? 0; ?></h3>
                                    <p>Total Depoimentos</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-comments"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3><?php echo $stats['pendentes'] ?? 0; ?></h3>
                                    <p>Pendentes</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3><?php echo $stats['aprovados'] ?? 0; ?></h3>
                                    <p>Aprovados</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3><?php echo $stats['reprovados'] ?? 0; ?></h3>
                                    <p>Reprovados</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
                        </div>
                    </div>            <!-- Depoimentos List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="fas fa-list"></i> Lista de Depoimentos</h5>
                            <div class="card-tools">
                                <select id="statusFilter" class="form-select form-select-sm">
                                    <option value="">Todos os Status</option>
                                    <option value="pendente">Pendente</option>
                                    <option value="aprovado">Aprovado</option>
                                    <option value="reprovado">Reprovado</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row" id="depoimentosContainer">
<?php foreach ($depoimentos as $depoimento): ?>
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-4 depoimento-item" data-status="<?php echo $depoimento['status']; ?>">
                                    <div class="card depoimento-card status-<?php echo $depoimento['status']; ?> h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($depoimento['nome_cliente'] ?? 'Anônimo'); ?>
                                            </h6>
                                            <span class="badge bg-<?php
                                                echo $depoimento['status'] === 'aprovado' ? 'success' :
                                                     ($depoimento['status'] === 'reprovado' ? 'danger' : 'warning');
                                            ?>">
                                                <?php echo ucfirst($depoimento['status'] ?? 'pendente'); ?>
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $depoimento['avaliacao'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                                <small class="text-muted ms-2">(<?php echo $depoimento['avaliacao'] ?? 0; ?>/5)</small>
                                            </div>
                                            <p class="card-text"><?php echo htmlspecialchars($depoimento['depoimento'] ?? ''); ?></p>
                                            <div class="text-muted small">
                                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($depoimento['cliente_email'] ?? 'N/A'); ?><br>
                                                <i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($depoimento['data_envio'] ?? 'now')); ?>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <div class="btn-group w-100" role="group">
                                                <button class="btn btn-success btn-sm" onclick="aprovarDepoimento(<?php echo $depoimento['id_depoimento']; ?>)">
                                                    <i class="fas fa-check"></i> Aprovar
                                                </button>
                                                <button class="btn btn-danger btn-sm" onclick="reprovarDepoimento(<?php echo $depoimento['id_depoimento']; ?>)">
                                                    <i class="fas fa-times"></i> Reprovar
                                                </button>
                                                <button class="btn btn-info btn-sm" onclick="visualizarDepoimento(<?php echo $depoimento['id_depoimento']; ?>)">
                                                    <i class="fas fa-eye"></i> Ver
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
<?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</div>
</body>
</html>
<?php } else { ?>
<!-- Versão Iframe -->
<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Moderação de Depoimentos - CONFINTER</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <style>
        body { background: #f4f6f9; margin: 0; padding: 20px; }
        .content-wrapper { margin: 0; background: transparent; }
        .card { box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }

        /* Estilos específicos para depoimentos */
        .depoimento-card {
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .depoimento-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .depoimento-card.status-aprovado {
            border-left: 4px solid #28a745;
        }

        .depoimento-card.status-reprovado {
            border-left: 4px solid #dc3545;
        }

        .depoimento-card.status-pendente {
            border-left: 4px solid #ffc107;
        }

        .card-text {
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 0.5rem;
        }

        .btn-group .btn {
            flex: 1;
            margin: 0 1px;
        }

        .btn-group .btn:first-child {
            margin-left: 0;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }

        /* Melhorar responsividade */
        @media (max-width: 768px) {
            .depoimento-card .card-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.5rem;
            }

            .depoimento-card .card-header .badge {
                align-self: flex-end;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn-group .btn {
                margin: 1px 0;
            }
        }

        /* Loading states */
        .btn.loading {
            opacity: 0.7;
            pointer-events: none;
            position: relative;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            width: 1rem;
            height: 1rem;
            top: 50%;
            left: 50%;
            margin-left: -0.5rem;
            margin-top: -0.5rem;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Alertas customizados */
        .alert-fixed {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* Filtro de status */
        #statusFilter {
            min-width: 150px;
        }

        /* Estrelas de avaliação */
        .fa-star {
            font-size: 0.9rem;
            margin-right: 2px;
        }
    </style>
</head>
<body>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo number_format($stats['total'] ?? 0); ?></h3>
                            <p>Total Depoimentos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-comments"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo number_format($stats['pendentes'] ?? 0); ?></h3>
                            <p>Pendentes</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo number_format($stats['aprovados'] ?? 0); ?></h3>
                            <p>Aprovados</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo number_format($stats['reprovados'] ?? 0); ?></h3>
                            <p>Reprovados</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Depoimentos List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="fas fa-list"></i> Lista de Depoimentos</h5>
                            <div class="card-tools">
                                <select id="statusFilter" class="form-select form-select-sm">
                                    <option value="">Todos os Status</option>
                                    <option value="pendente">Pendente</option>
                                    <option value="aprovado">Aprovado</option>
                                    <option value="reprovado">Reprovado</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row" id="depoimentosContainer">
<?php foreach ($depoimentos as $depoimento): ?>
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-4 depoimento-item" data-status="<?php echo $depoimento['status']; ?>">
                                    <div class="card depoimento-card status-<?php echo $depoimento['status']; ?> h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($depoimento['nome_cliente'] ?? 'Anônimo'); ?>
                                            </h6>
                                            <span class="badge bg-<?php
                                                echo $depoimento['status'] === 'aprovado' ? 'success' :
                                                     ($depoimento['status'] === 'reprovado' ? 'danger' : 'warning');
                                            ?>">
                                                <?php echo ucfirst($depoimento['status'] ?? 'pendente'); ?>
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $depoimento['avaliacao'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                                <small class="text-muted ms-2">(<?php echo $depoimento['avaliacao'] ?? 0; ?>/5)</small>
                                            </div>
                                            <p class="card-text"><?php echo htmlspecialchars($depoimento['depoimento'] ?? ''); ?></p>
                                            <div class="text-muted small">
                                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($depoimento['cliente_email'] ?? 'N/A'); ?><br>
                                                <i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($depoimento['data_envio'] ?? 'now')); ?>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <div class="btn-group w-100" role="group">
                                                <button class="btn btn-success btn-sm" onclick="aprovarDepoimento(<?php echo $depoimento['id_depoimento']; ?>)">
                                                    <i class="fas fa-check"></i> Aprovar
                                                </button>
                                                <button class="btn btn-danger btn-sm" onclick="reprovarDepoimento(<?php echo $depoimento['id_depoimento']; ?>)">
                                                    <i class="fas fa-times"></i> Reprovar
                                                </button>
                                                <button class="btn btn-info btn-sm" onclick="visualizarDepoimento(<?php echo $depoimento['id_depoimento']; ?>)">
                                                    <i class="fas fa-eye"></i> Ver
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
<?php endforeach; ?>
        </div>
    </section>
</div>

<!-- Modal para visualizar depoimento -->
<div class="modal fade" id="depoimentoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Visualizar Depoimento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="depoimentoModalBody">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts (iframe) -->
<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js'></script>
</body>
</html>
<?php } ?>    </div>
        </div>
    </div>
</div>

<script>
        $(document).ready(function() {
            // Filtro de status
            $('#statusFilter').change(function() {
                var status = $(this).val();
                if (status === '') {
                    $('.depoimento-item').show();
                } else {
                    $('.depoimento-item').hide();
                    $('.depoimento-item[data-status="' + status + '"]').show();
                }
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });

        function aprovarDepoimento(id) {
            if (confirm('Tem certeza que deseja aprovar este depoimento?')) {
                // Show loading state
                const btn = event.target.closest('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.classList.add('loading');
                btn.disabled = true;

                $.post('processar_depoimento.php', {
                    action: 'aprovar',
                    id: id
                }, function(response) {
                    if (response.success) {
                        showAlert('Depoimento aprovado com sucesso!', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert('Erro ao aprovar depoimento: ' + (response.message || 'Erro desconhecido'), 'danger');
                        btn.innerHTML = originalText;
                        btn.classList.remove('loading');
                        btn.disabled = false;
                    }
                }, 'json').fail(function(xhr, status, error) {
                    showAlert('Erro na comunicação com o servidor: ' + error, 'danger');
                    btn.innerHTML = originalText;
                    btn.classList.remove('loading');
                    btn.disabled = false;
                });
            }
        }

        function reprovarDepoimento(id) {
            if (confirm('Tem certeza que deseja reprovar este depoimento?')) {
                // Show loading state
                const btn = event.target.closest('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.classList.add('loading');
                btn.disabled = true;

                $.post('processar_depoimento.php', {
                    action: 'reprovar',
                    id: id
                }, function(response) {
                    if (response.success) {
                        showAlert('Depoimento reprovado com sucesso!', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert('Erro ao reprovar depoimento: ' + (response.message || 'Erro desconhecido'), 'danger');
                        btn.innerHTML = originalText;
                        btn.classList.remove('loading');
                        btn.disabled = false;
                    }
                }, 'json').fail(function(xhr, status, error) {
                    showAlert('Erro na comunicação com o servidor: ' + error, 'danger');
                    btn.innerHTML = originalText;
                    btn.classList.remove('loading');
                    btn.disabled = false;
                });
            }
        }

        function visualizarDepoimento(id) {
            $('#depoimentoModalBody').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Carregando...</p></div>');
            $('#depoimentoModal').modal('show');

            $.get('ver_depoimento.php', { id: id }, function(data) {
                $('#depoimentoModalBody').html(data);
            }).fail(function(xhr, status, error) {
                $('#depoimentoModalBody').html('<div class="alert alert-danger">Erro ao carregar depoimento: ' + error + '</div>');
            });
        }

        function showAlert(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';

            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show alert-fixed" role="alert">
                    <i class="fas ${iconClass} mr-2"></i>
                    <strong>${type === 'success' ? 'Sucesso!' : 'Erro!'}</strong> ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;

            $('body').append(alertHtml);

            // Auto-remove after 5 seconds
            setTimeout(function() {
                $('.alert-fixed').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        }

        // Ajustar altura do iframe quando carregado
        <?php if ($is_iframe): ?>
        window.addEventListener('load', function() {
            setTimeout(function() {
                const height = document.body.scrollHeight;
                if (window.parent) {
                    window.parent.postMessage({
                        type: 'resize-iframe',
                        height: height + 50
                    }, '*');
                }
            }, 100);
        });
        <?php endif; ?>
    </script>

<?php
$conexao->close();
?>
