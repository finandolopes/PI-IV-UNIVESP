<?php
session_start();
include_once('../php/conexao.php');

// Verificar se usuário está logado e é admin
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Verificar se é admin
$user_query = "SELECT perfil FROM adm WHERE usuario = ?";
$stmt = $conexao->prepare($user_query);
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

if (!$user_data || $user_data['perfil'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $solicitacao_id = $_POST['solicitacao_id'];

        if ($_POST['action'] === 'processar') {
            $nova_senha = $_POST['nova_senha'];

            // Buscar dados da solicitação
            $query_solicitacao = "SELECT * FROM reset_senha_solicitacoes WHERE id = ? AND status = 'pendente'";
            $stmt = $conexao->prepare($query_solicitacao);
            $stmt->bind_param("i", $solicitacao_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $solicitacao = $result->fetch_assoc();

                // Atualizar senha do usuário
                $senha_hash = md5($nova_senha);
                $update_usuario = "UPDATE adm SET senha = ? WHERE id_usuario = ?";
                $stmt_update = $conexao->prepare($update_usuario);
                $stmt_update->bind_param("si", $senha_hash, $solicitacao['usuario_id']);

                if ($stmt_update->execute()) {
                    // Atualizar status da solicitação
                    $update_solicitacao = "UPDATE reset_senha_solicitacoes SET status = 'processado', nova_senha = ?, data_processamento = NOW() WHERE id = ?";
                    $stmt_solicitacao = $conexao->prepare($update_solicitacao);
                    $stmt_solicitacao->bind_param("si", $nova_senha, $solicitacao_id);
                    $stmt_solicitacao->execute();

                    $_SESSION['success'] = "Senha resetada com sucesso! Nova senha: " . $nova_senha;
                } else {
                    $_SESSION['error'] = "Erro ao atualizar senha do usuário.";
                }
            } else {
                $_SESSION['error'] = "Solicitação não encontrada ou já processada.";
            }
        } elseif ($_POST['action'] === 'cancelar') {
            // Cancelar solicitação
            $update_cancelar = "UPDATE reset_senha_solicitacoes SET status = 'cancelado' WHERE id = ?";
            $stmt_cancelar = $conexao->prepare($update_cancelar);
            $stmt_cancelar->bind_param("i", $solicitacao_id);
            $stmt_cancelar->execute();

            $_SESSION['success'] = "Solicitação cancelada com sucesso.";
        }
    }
}

// Estatísticas para os cards
$query_pendentes = "SELECT COUNT(*) as total FROM reset_senha_solicitacoes WHERE status = 'pendente'";
$result_pendentes = $conexao->query($query_pendentes);
$pendentes = 0;
if ($result_pendentes) {
    $row = $result_pendentes->fetch_assoc();
    $pendentes = $row ? $row['total'] : 0;
}

$query_processados_hoje = "SELECT COUNT(*) as total FROM reset_senha_solicitacoes WHERE status = 'processado' AND DATE(data_processamento) = CURDATE()";
$result_processados_hoje = $conexao->query($query_processados_hoje);
$processados_hoje = 0;
if ($result_processados_hoje) {
    $row = $result_processados_hoje->fetch_assoc();
    $processados_hoje = $row ? $row['total'] : 0;
}

$query_total_resets = "SELECT COUNT(*) as total FROM reset_senha_solicitacoes WHERE status = 'processado'";
$result_total_resets = $conexao->query($query_total_resets);
$total_resets = 0;
if ($result_total_resets) {
    $row = $result_total_resets->fetch_assoc();
    $total_resets = $row ? $row['total'] : 0;
}

$query_tempo_medio = "SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(data_processamento, data_solicitacao)))) as media FROM reset_senha_solicitacoes WHERE status = 'processado'";
$result_tempo_medio = $conexao->query($query_tempo_medio);
$tempo_medio = null;
if ($result_tempo_medio) {
    $row = $result_tempo_medio->fetch_assoc();
    $tempo_medio = $row ? $row['media'] : null;
}

// Buscar solicitações pendentes
$query_solicitacoes = "SELECT r.*, u.nome, u.usuario, u.email as email_usuario
                      FROM reset_senha_solicitacoes r
                      LEFT JOIN adm u ON r.usuario_id = u.id_usuario
                      WHERE r.status = 'pendente'
                      ORDER BY r.data_solicitacao DESC";
$result_solicitacoes = $conexao->query($query_solicitacoes);
$solicitacoes = [];
if ($result_solicitacoes) {
    while ($row = $result_solicitacoes->fetch_assoc()) {
        $solicitacoes[] = $row;
    }
}

// Buscar solicitações processadas (últimas 10)
$query_processadas = "SELECT r.*, u.nome, u.usuario, u.email as email_usuario
                     FROM reset_senha_solicitacoes r
                     LEFT JOIN adm u ON r.usuario_id = u.id_usuario
                     WHERE r.status != 'pendente'
                     ORDER BY r.data_processamento DESC
                     LIMIT 10";
$result_processadas = $conexao->query($query_processadas);
$solicitacoes_processadas = [];
if ($result_processadas) {
    while ($row = $result_processadas->fetch_assoc()) {
        $solicitacoes_processadas[] = $row;
    }
}

$is_iframe = isset($_GET['iframe']) || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin.php') !== false);
?>

<?php if (!$is_iframe): ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CONFINTER - Gerenciar Reset de Senha</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <style>
        .real-time-card {
            transition: all 0.3s ease;
        }
        .real-time-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-key mr-2"></i>
                        Gerenciar Reset de Senha
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
                        <li class="breadcrumb-item active">Reset de Senha</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning real-time-card">
                        <div class="inner">
                            <h3><?php echo number_format($pendentes); ?></h3>
                            <p>Solicitações Pendentes</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <a href="#solicitacoesPendentes" class="small-box-footer">
                            Ver Detalhes <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success real-time-card">
                        <div class="inner">
                            <h3><?php echo number_format($processados_hoje); ?></h3>
                            <p>Processados Hoje</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info real-time-card">
                        <div class="inner">
                            <h3><?php echo number_format($total_resets); ?></h3>
                            <p>Total de Resets</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger real-time-card">
                        <div class="inner">
                            <h3><?php echo $tempo_medio ?: '00:00:00'; ?></h3>
                            <p>Tempo Médio</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensagens -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-check-circle mr-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-triangle mr-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Solicitações Pendentes -->
            <div class="card" id="solicitacoesPendentes">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock mr-1"></i>
                        Solicitações Pendentes
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-warning"><?php echo count($solicitacoes); ?> pendente(s)</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($solicitacoes)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                            <h4>Nenhuma solicitação pendente</h4>
                            <p>Todas as solicitações foram processadas.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table id="tabelaPendentes" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Usuário</th>
                                        <th>Email</th>
                                        <th>Data Solicitação</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($solicitacoes as $solicitacao): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($solicitacao['nome_usuario'] ?? $solicitacao['usuario']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($solicitacao['usuario']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($solicitacao['email']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-success btn-sm" onclick="processarReset(<?php echo $solicitacao['id']; ?>, '<?php echo htmlspecialchars($solicitacao['nome_usuario'] ?? $solicitacao['usuario']); ?>')">
                                                    <i class="fas fa-key"></i> Resetar
                                                </button>
                                                <button class="btn btn-danger btn-sm" onclick="cancelarSolicitacao(<?php echo $solicitacao['id']; ?>)">
                                                    <i class="fas fa-times"></i> Cancelar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Solicitações Processadas -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-1"></i>
                        Histórico de Resets
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (empty($solicitacoes_processadas)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-history fa-3x mb-3 text-muted"></i>
                            <h4>Nenhum reset processado</h4>
                            <p>Ainda não foram realizados resets de senha.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table id="tabelaProcessadas" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Usuário</th>
                                        <th>Email</th>
                                        <th>Data Solicitação</th>
                                        <th>Data Processamento</th>
                                        <th>Status</th>
                                        <th>Nova Senha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($solicitacoes_processadas as $solicitacao): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($solicitacao['nome_usuario'] ?? $solicitacao['usuario']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($solicitacao['usuario']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($solicitacao['email']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])); ?></td>
                                        <td><?php echo $solicitacao['data_processamento'] ? date('d/m/Y H:i', strtotime($solicitacao['data_processamento'])) : '-'; ?></td>
                                        <td>
                                            <?php
                                            $status_class = $solicitacao['status'] === 'processado' ? 'success' : 'danger';
                                            $status_text = $solicitacao['status'] === 'processado' ? 'Processado' : 'Cancelado';
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($solicitacao['status'] === 'processado' && $solicitacao['nova_senha']): ?>
                                                <code class="bg-light px-2 py-1 rounded"><?php echo htmlspecialchars($solicitacao['nova_senha']); ?></code>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include 'footer.php'; ?>

<!-- Modal para Processar Reset -->
<div class="modal fade" id="processarResetModal" tabindex="-1" role="dialog" aria-labelledby="processarResetModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="processarResetModalLabel">
                    <i class="fas fa-key mr-2"></i>Resetar Senha
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="solicitacao_id" id="solicitacao_id">
                    <input type="hidden" name="action" value="processar">
                    <div class="form-group">
                        <label for="usuario_info">
                            <i class="fas fa-user mr-1"></i>Usuário:
                        </label>
                        <p class="mb-3"><strong id="usuario_nome"></strong></p>
                    </div>
                    <div class="form-group">
                        <label for="nova_senha">
                            <i class="fas fa-lock mr-1"></i>Nova Senha:
                        </label>
                        <input type="password" class="form-control" id="nova_senha" name="nova_senha" required>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle mr-1"></i>A senha será mostrada ao usuário após o reset.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-key mr-1"></i>Resetar Senha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Cancelar Solicitação -->
<div class="modal fade" id="cancelarModal" tabindex="-1" role="dialog" aria-labelledby="cancelarModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelarModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2 text-warning"></i>Cancelar Solicitação
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="solicitacao_id" id="cancelar_solicitacao_id">
                    <input type="hidden" name="action" value="cancelar">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Atenção!</strong> Tem certeza que deseja cancelar esta solicitação de reset de senha?
                        <br><small>Esta ação não pode ser desfeita.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-arrow-left mr-1"></i>Não, voltar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times mr-1"></i>Sim, cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTables
    $('#tabelaPendentes').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json"
        }
    });

    $('#tabelaProcessadas').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json"
        }
    });
});

function processarReset(id, nome) {
    document.getElementById('solicitacao_id').value = id;
    document.getElementById('usuario_nome').textContent = nome;
    $('#processarResetModal').modal('show');
}

function cancelarSolicitacao(id) {
    document.getElementById('cancelar_solicitacao_id').value = id;
    $('#cancelarModal').modal('show');
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

</body>
</html>
<?php endif; ?>