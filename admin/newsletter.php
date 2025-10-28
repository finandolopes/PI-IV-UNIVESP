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

// Buscar todos os emails cadastrados
$query = "SELECT * FROM newsletter ORDER BY data_inscricao DESC";
$result = mysqli_query($conexao, $query);
$emails = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $emails[] = $row;
    }
}

// Estatísticas da newsletter
$stats_query = "
    SELECT
        COUNT(*) as total_emails,
        SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END) as ativos,
        SUM(CASE WHEN data_inscricao >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as ultimos_30_dias
    FROM newsletter
";
$stats_result = mysqli_query($conexao, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

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
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo number_format($stats['total_emails'] ?? 0, 0, ',', '.'); ?></h3>
                            <p>Total de Emails</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo number_format($stats['ativos'] ?? 0, 0, ',', '.'); ?></h3>
                            <p>Emails Ativos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo number_format($stats['ultimos_30_dias'] ?? 0, 0, ',', '.'); ?></h3>
                            <p>Últimos 30 Dias</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Emails -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="fas fa-list"></i> Lista de Emails Cadastrados</h5>
                    <div class="card-tools">
                        <select id="statusFilter" class="form-select form-select-sm mr-2">
                            <option value="">Todos os Status</option>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="newsletterTable" class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Data de Cadastro</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($emails)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    <div class="py-4">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>Nenhum email cadastrado ainda.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($emails as $email): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($email['email']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($email['data_inscricao'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $email['status'] === 'ativo' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($email['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-<?php echo $email['status'] === 'ativo' ? 'warning' : 'success'; ?>"
                                                onclick="toggleStatus(<?php echo $email['id']; ?>, '<?php echo $email['status']; ?>')">
                                            <i class="fas fa-<?php echo $email['status'] === 'ativo' ? 'ban' : 'check'; ?>"></i>
                                            <?php echo $email['status'] === 'ativo' ? 'Desativar' : 'Ativar'; ?>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#newsletterTable').DataTable({
        'language': {
            'lengthMenu': 'Mostrar _MENU_ registros por página',
            'zeroRecords': 'Nenhum registro encontrado',
            'info': 'Mostrando página _PAGE_ de _PAGES_',
            'infoEmpty': 'Nenhum registro disponível',
            'infoFiltered': '(filtrado de _MAX_ registros totais)',
            'search': 'Buscar:',
            'paginate': {
                'first': 'Primeiro',
                'last': 'Último',
                'next': 'Próximo',
                'previous': 'Anterior'
            }
        },
        'pageLength': 25,
        'responsive': true,
        'order': [[1, 'desc']]
    });

    // Filtro de status
    $('#statusFilter').change(function() {
        var status = $(this).val();
        if (status === '') {
            $('#newsletterTable tbody tr').show();
        } else {
            $('#newsletterTable tbody tr').hide();
            $('#newsletterTable tbody tr').each(function() {
                var rowStatus = $(this).find('td:nth-child(3) .badge').text().toLowerCase();
                if (rowStatus === status) {
                    $(this).show();
                }
            });
        }
    });
});

function toggleStatus(id, currentStatus) {
    if (confirm('Tem certeza que deseja ' + (currentStatus === 'ativo' ? 'desativar' : 'ativar') + ' este email?')) {
        // AJAX para alterar status
        $.post('../php/toggle_newsletter_status.php', {
            id: id,
            status: currentStatus
        })
        .done(function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Erro ao alterar status: ' + response.message);
            }
        })
        .fail(function() {
            alert('Erro na comunicação com o servidor.');
        });
    }
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
    <title>Gerenciar Newsletter - CONFINTER</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <link rel='stylesheet' href='https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css'>
    <style>
        body { background: #f4f6f9; margin: 0; padding: 20px; }
        .content-wrapper { margin: 0; background: transparent; }
        .card { box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
    </style>
</head>
<body>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo number_format($stats['total_emails'] ?? 0, 0, ',', '.'); ?></h3>
                            <p>Total de Emails</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo number_format($stats['ativos'] ?? 0, 0, ',', '.'); ?></h3>
                            <p>Emails Ativos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo number_format($stats['ultimos_30_dias'] ?? 0, 0, ',', '.'); ?></h3>
                            <p>Últimos 30 Dias</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Emails -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="fas fa-list"></i> Lista de Emails Cadastrados</h5>
                    <div class="card-tools">
                        <select id="statusFilter" class="form-select form-select-sm mr-2">
                            <option value="">Todos os Status</option>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="newsletterTable" class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Data de Cadastro</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($emails)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    <div class="py-4">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>Nenhum email cadastrado ainda.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($emails as $email): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($email['email']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($email['data_inscricao'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $email['status'] === 'ativo' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($email['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-<?php echo $email['status'] === 'ativo' ? 'warning' : 'success'; ?>"
                                                onclick="toggleStatus(<?php echo $email['id']; ?>, '<?php echo $email['status']; ?>')">
                                            <i class="fas fa-<?php echo $email['status'] === 'ativo' ? 'ban' : 'check'; ?>"></i>
                                            <?php echo $email['status'] === 'ativo' ? 'Desativar' : 'Ativar'; ?>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

<!-- Scripts (iframe) -->
<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js'></script>
<script src='https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js'></script>
<script src='https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js'></script>
</body>
</html>
<?php } ?>

<script>
    $(document).ready(function() {
        $('#newsletterTable').DataTable({
            'language': {
                'lengthMenu': 'Mostrar _MENU_ registros por página',
                'zeroRecords': 'Nenhum registro encontrado',
                'info': 'Mostrando página _PAGE_ de _PAGES_',
                'infoEmpty': 'Nenhum registro disponível',
                'infoFiltered': '(filtrado de _MAX_ registros totais)',
                'search': 'Buscar:',
                'paginate': {
                    'first': 'Primeiro',
                    'last': 'Último',
                    'next': 'Próximo',
                    'previous': 'Anterior'
                }
            },
            'pageLength': 25,
            'responsive': true,
            'order': [[1, 'desc']]
        });

        // Filtro de status
        $('#statusFilter').change(function() {
            var status = $(this).val();
            if (status === '') {
                $('#newsletterTable tbody tr').show();
            } else {
                $('#newsletterTable tbody tr').hide();
                $('#newsletterTable tbody tr').each(function() {
                    var rowStatus = $(this).find('td:nth-child(3) .badge').text().toLowerCase();
                    if (rowStatus === status) {
                        $(this).show();
                    }
                });
            }
        });
    });

    function toggleStatus(id, currentStatus) {
        if (confirm('Tem certeza que deseja ' + (currentStatus === 'ativo' ? 'desativar' : 'ativar') + ' este email?')) {
            // AJAX para alterar status
            $.post('../php/toggle_newsletter_status.php', {
                id: id,
                status: currentStatus
            })
            .done(function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Erro ao alterar status: ' + response.message);
                }
            })
            .fail(function() {
                alert('Erro na comunicação com o servidor.');
            });
        }
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
mysqli_close($conexao);
?>