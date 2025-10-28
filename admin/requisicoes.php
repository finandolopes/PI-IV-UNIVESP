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

// Conexão já estabelecida pelo arquivo conexao.php incluído acima

// Filtros
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'todos';
$tipo_filter = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';

// Query base
$query = "SELECT r.*, c.nome as cliente_nome, c.email as cliente_email,
          a.nome as analista_nome
          FROM requisicoes r
          LEFT JOIN clientes c ON r.id_cliente = c.id_cliente
          LEFT JOIN adm a ON r.analista_id = a.id_usuario
          WHERE 1=1";

$params = [];
$types = "";

// Aplicar filtros
if (!empty($search)) {
    $query .= " AND (c.nome LIKE ? OR c.email LIKE ? OR r.tipo LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if ($status_filter !== 'todos') {
    $query .= " AND r.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($tipo_filter !== 'todos') {
    $query .= " AND r.categoria = ?";
    $params[] = $tipo_filter;
    $types .= "s";
}

$query .= " ORDER BY r.data_hora DESC";

$stmt = $conexao->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$requisicoes = $result->fetch_all(MYSQLI_ASSOC);

// Calcular estatísticas
$stats = [
    'total' => count($requisicoes),
    'pendente' => 0,
    'em_analise' => 0,
    'aprovado' => 0,
    'reprovado' => 0,
    'cancelado' => 0,
    'valor_total' => 0
];

foreach ($requisicoes as $req) {
    $stats[$req['status']]++;
    if ($req['status'] === 'aprovado' && $req['valor_solicitado']) {
        $stats['valor_total'] += $req['valor_solicitado'];
    }
}

// Processar ações POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $request_id = $_POST['request_id'] ?? 0;

    if ($action === 'approve' && $request_id) {
        $stmt = $conexao->prepare("UPDATE requisicoes SET status = 'aprovado', analista_id = ?, data_analise = NOW() WHERE id_requisicao = ?");
        $stmt->bind_param("ii", $_SESSION['usuario_id'], $request_id);
        $stmt->execute();
        $_SESSION['success'] = 'Requisição aprovada com sucesso!';
        header('Location: ' . $_SERVER['PHP_SELF'] . ($is_iframe ? '?iframe=1' : ''));
        exit;
    }

    if ($action === 'reject' && $request_id) {
        $stmt = $conexao->prepare("UPDATE requisicoes SET status = 'reprovado', analista_id = ?, data_analise = NOW() WHERE id_requisicao = ?");
        $stmt->bind_param("ii", $_SESSION['usuario_id'], $request_id);
        $stmt->execute();
        $_SESSION['success'] = 'Requisição rejeitada com sucesso!';
        header('Location: ' . $_SERVER['PHP_SELF'] . ($is_iframe ? '?iframe=1' : ''));
        exit;
    }

    if ($action === 'update_status' && $request_id) {
        $new_status = $_POST['status'] ?? '';
        $observacoes = $_POST['observacoes'] ?? '';

        $stmt = $conexao->prepare("UPDATE requisicoes SET status = ?, observacoes = ?, analista_id = ?, data_analise = NOW() WHERE id_requisicao = ?");
        $stmt->bind_param("ssii", $new_status, $observacoes, $_SESSION['usuario_id'], $request_id);
        $stmt->execute();
        $_SESSION['success'] = 'Status atualizado com sucesso!';
        header('Location: ' . $_SERVER['PHP_SELF'] . ($is_iframe ? '?iframe=1' : ''));
        exit;
    }
}

// Função para calcular score de crédito
function calcularScoreCredito($requisicao) {
    $score = 0;

    // Base score por categoria
    switch ($requisicao['categoria']) {
        case 'Servidor Público':
            $score += 800;
            break;
        case 'Aposentado':
            $score += 750;
            break;
        case 'Pensionista':
            $score += 700;
            break;
        default:
            $score += 600;
    }

    // Ajustes por valor solicitado
    $valor = $requisicao['valor_solicitado'] ?? 0;
    if ($valor <= 5000) $score += 100;
    elseif ($valor <= 15000) $score += 50;
    elseif ($valor <= 30000) $score += 0;
    else $score -= 50;

    // Ajustes por prazo
    $prazo = $requisicao['prazo'] ?? 12;
    if ($prazo <= 12) $score += 50;
    elseif ($prazo <= 24) $score += 25;
    elseif ($prazo <= 36) $score += 0;
    else $score -= 25;

    return min(1000, max(300, $score));
}

// Função para determinar risco
function determinarRisco($score) {
    if ($score >= 800) return ['nivel' => 'Baixo', 'cor' => 'success', 'icone' => 'check-circle'];
    if ($score >= 650) return ['nivel' => 'Médio', 'cor' => 'warning', 'icone' => 'exclamation-triangle'];
    return ['nivel' => 'Alto', 'cor' => 'danger', 'icone' => 'times-circle'];
}

if (!$is_iframe) {
    // Versão completa com navbar e sidebar
    include 'navbar.php';
    include 'sidebar.php';
?>
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <!-- Alertas -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Cards de Estatísticas -->
            <div class="row mb-4">
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo number_format($stats['total']); ?></h3>
                            <p>Total</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo number_format($stats['pendente']); ?></h3>
                            <p>Pendentes</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3><?php echo number_format($stats['em_analise']); ?></h3>
                            <p>Em Análise</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo number_format($stats['aprovado']); ?></h3>
                            <p>Aprovadas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo number_format($stats['reprovado']); ?></h3>
                            <p>Reprovadas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3>R$ <?php echo number_format($stats['valor_total'], 0, ',', '.'); ?></h3>
                            <p>Valor Aprovado</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter mr-1"></i>
                        Filtros e Busca
                    </h3>
                </div>
                <div class="card-body">
                    <form method="get" action="">
                        <?php if ($is_iframe): ?>
                            <input type="hidden" name="iframe" value="1">
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search">Buscar:</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                           value="<?php echo htmlspecialchars($search); ?>"
                                           placeholder="Nome, email ou tipo...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Status:</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="todos" <?php echo $status_filter === 'todos' ? 'selected' : ''; ?>>Todos</option>
                                        <option value="pendente" <?php echo $status_filter === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                        <option value="em_analise" <?php echo $status_filter === 'em_analise' ? 'selected' : ''; ?>>Em Análise</option>
                                        <option value="aprovado" <?php echo $status_filter === 'aprovado' ? 'selected' : ''; ?>>Aprovado</option>
                                        <option value="reprovado" <?php echo $status_filter === 'reprovado' ? 'selected' : ''; ?>>Reprovado</option>
                                        <option value="cancelado" <?php echo $status_filter === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tipo">Categoria:</label>
                                    <select class="form-control" id="tipo" name="tipo">
                                        <option value="todos" <?php echo $tipo_filter === 'todos' ? 'selected' : ''; ?>>Todas</option>
                                        <option value="Aposentado" <?php echo $tipo_filter === 'Aposentado' ? 'selected' : ''; ?>>Aposentado</option>
                                        <option value="Pensionista" <?php echo $tipo_filter === 'Pensionista' ? 'selected' : ''; ?>>Pensionista</option>
                                        <option value="Servidor Público" <?php echo $tipo_filter === 'Servidor Público' ? 'selected' : ''; ?>>Servidor Público</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-search"></i> Filtrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabela de Requisições -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="fas fa-table"></i> Requisições (<?php echo count($requisicoes); ?>)</h5>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table id="requisicoesTable" class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Tipo</th>
                                        <th>Valor</th>
                                        <th>Score</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requisicoes as $req): ?>
                                        <?php
                                        $score = calcularScoreCredito($req);
                                        $risco = determinarRisco($score);
                                        $status_colors = [
                                            'pendente' => 'warning',
                                            'em_analise' => 'primary',
                                            'aprovado' => 'success',
                                            'reprovado' => 'danger',
                                            'cancelado' => 'secondary'
                                        ];
                                        ?>
                                        <tr>
                                            <td><?php echo $req['id_requisicao']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($req['cliente_nome'] ?? 'N/A'); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($req['cliente_email'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($req['tipo'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if (isset($req['valor_solicitado']) && $req['valor_solicitado']): ?>
                                                    R$ <?php echo number_format($req['valor_solicitado'], 2, ',', '.'); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Não informado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $risco['cor']; ?>">
                                                    <i class="fas fa-<?php echo $risco['icone']; ?>"></i>
                                                    <?php echo $score; ?> - <?php echo $risco['nivel']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $status_colors[$req['status']] ?? 'secondary'; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $req['status'] ?? 'desconhecido')); ?>
                                                </span>
                                                <?php if (isset($req['analista_nome']) && $req['analista_nome']): ?>
                                                    <br><small>por <?php echo htmlspecialchars($req['analista_nome']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($req['data_hora'] ?? 'now')); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-info" onclick="verDetalhes(<?php echo $req['id_requisicao']; ?>)" title="Ver Detalhes">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if (($req['status'] ?? '') === 'pendente'): ?>
                                                        <button class="btn btn-sm btn-success" onclick="aprovarRequisicao(<?php echo $req['id_requisicao']; ?>)" title="Aprovar">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" onclick="rejeitarRequisicao(<?php echo $req['id_requisicao']; ?>)" title="Rejeitar">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-warning" onclick="editarStatus(<?php echo $req['id_requisicao']; ?>)" title="Editar Status">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>

<?php } else { ?>
<!-- Versão Iframe -->
<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Requisições - CONFINTER</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <link rel='stylesheet' href='https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css'>
    <style>
        body { background: #f4f6f9; margin: 0; padding: 20px; }
        .content-wrapper { margin: 0; background: transparent; }
        .card { box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }

        /* Estilos específicos para requisições */
        .requisicao-card {
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .requisicao-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .requisicao-card.status-aprovado {
            border-left: 4px solid #28a745;
        }

        .requisicao-card.status-reprovado {
            border-left: 4px solid #dc3545;
        }

        .requisicao-card.status-pendente {
            border-left: 4px solid #ffc107;
        }

        .requisicao-card.status-em_analise {
            border-left: 4px solid #007bff;
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
            .requisicao-card .card-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.5rem;
            }

            .requisicao-card .card-header .badge {
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
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <!-- Alertas -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Cards de Estatísticas -->
            <div class="row mb-4">
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo number_format($stats['total']); ?></h3>
                            <p>Total</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo number_format($stats['pendente']); ?></h3>
                            <p>Pendentes</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3><?php echo number_format($stats['em_analise']); ?></h3>
                            <p>Em Análise</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo number_format($stats['aprovado']); ?></h3>
                            <p>Aprovadas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo number_format($stats['reprovado']); ?></h3>
                            <p>Reprovadas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3>R$ <?php echo number_format($stats['valor_total'], 0, ',', '.'); ?></h3>
                            <p>Valor Aprovado</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter mr-1"></i>
                        Filtros e Busca
                    </h3>
                </div>
                <div class="card-body">
                    <form method="get" action="">
                        <input type="hidden" name="iframe" value="1">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search">Buscar:</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                           value="<?php echo htmlspecialchars($search); ?>"
                                           placeholder="Nome, email ou tipo...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Status:</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="todos" <?php echo $status_filter === 'todos' ? 'selected' : ''; ?>>Todos</option>
                                        <option value="pendente" <?php echo $status_filter === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                        <option value="em_analise" <?php echo $status_filter === 'em_analise' ? 'selected' : ''; ?>>Em Análise</option>
                                        <option value="aprovado" <?php echo $status_filter === 'aprovado' ? 'selected' : ''; ?>>Aprovado</option>
                                        <option value="reprovado" <?php echo $status_filter === 'reprovado' ? 'selected' : ''; ?>>Reprovado</option>
                                        <option value="cancelado" <?php echo $status_filter === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tipo">Categoria:</label>
                                    <select class="form-control" id="tipo" name="tipo">
                                        <option value="todos" <?php echo $tipo_filter === 'todos' ? 'selected' : ''; ?>>Todas</option>
                                        <option value="Aposentado" <?php echo $tipo_filter === 'Aposentado' ? 'selected' : ''; ?>>Aposentado</option>
                                        <option value="Pensionista" <?php echo $tipo_filter === 'Pensionista' ? 'selected' : ''; ?>>Pensionista</option>
                                        <option value="Servidor Público" <?php echo $tipo_filter === 'Servidor Público' ? 'selected' : ''; ?>>Servidor Público</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-search"></i> Filtrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabela de Requisições -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="fas fa-table"></i> Requisições (<?php echo count($requisicoes); ?>)</h5>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table id="requisicoesTable" class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Tipo</th>
                                        <th>Valor</th>
                                        <th>Score</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requisicoes as $req): ?>
                                        <?php
                                        $score = calcularScoreCredito($req);
                                        $risco = determinarRisco($score);
                                        $status_colors = [
                                            'pendente' => 'warning',
                                            'em_analise' => 'primary',
                                            'aprovado' => 'success',
                                            'reprovado' => 'danger',
                                            'cancelado' => 'secondary'
                                        ];
                                        ?>
                                        <tr>
                                            <td><?php echo $req['id_requisicao']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($req['cliente_nome'] ?? 'N/A'); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($req['cliente_email'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($req['tipo'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if (isset($req['valor_solicitado']) && $req['valor_solicitado']): ?>
                                                    R$ <?php echo number_format($req['valor_solicitado'], 2, ',', '.'); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Não informado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $risco['cor']; ?>">
                                                    <i class="fas fa-<?php echo $risco['icone']; ?>"></i>
                                                    <?php echo $score; ?> - <?php echo $risco['nivel']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $status_colors[$req['status']] ?? 'secondary'; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $req['status'] ?? 'desconhecido')); ?>
                                                </span>
                                                <?php if (isset($req['analista_nome']) && $req['analista_nome']): ?>
                                                    <br><small>por <?php echo htmlspecialchars($req['analista_nome']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($req['data_hora'] ?? 'now')); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-info" onclick="verDetalhes(<?php echo $req['id_requisicao']; ?>)" title="Ver Detalhes">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if (($req['status'] ?? '') === 'pendente'): ?>
                                                        <button class="btn btn-sm btn-success" onclick="aprovarRequisicao(<?php echo $req['id_requisicao']; ?>)" title="Aprovar">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" onclick="rejeitarRequisicao(<?php echo $req['id_requisicao']; ?>)" title="Rejeitar">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-warning" onclick="editarStatus(<?php echo $req['id_requisicao']; ?>)" title="Editar Status">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal de Detalhes -->
<div class="modal fade" id="modalDetalhes" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes da Requisição</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Modal de Editar Status -->
<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="">
                <input type="hidden" name="iframe" value="1">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="request_id" id="status_request_id">

                    <div class="form-group">
                        <label for="status_select">Novo Status</label>
                        <select class="form-control" name="status" id="status_select" required>
                            <option value="pendente">Pendente</option>
                            <option value="em_analise">Em Análise</option>
                            <option value="aprovado">Aprovado</option>
                            <option value="reprovado">Reprovado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status_observacoes">Observações</label>
                        <textarea class="form-control" name="observacoes" id="status_observacoes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts (iframe) -->
<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js'></script>
<script src='https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js'></script>
<script src='https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js'></script>

<script>
$(document).ready(function() {
    // Inicializar DataTable com configuração mais robusta
    $('#requisicoesTable').DataTable({
        'language': {
            'url': '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
        },
        'pageLength': 25,
        'responsive': true,
        'order': [[0, 'desc']],
        'columnDefs': [
            {
                'orderable': false,
                'targets': [7] // Coluna de Ações (0-indexed)
            }
        ],
        'initComplete': function() {
            console.log('DataTable initialized successfully');
        }
    });

    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

function verDetalhes(id) {
    $('#modalContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Carregando...</p></div>');
    $('#modalDetalhes').modal('show');

    $.get('ver_requisicao.php?id=' + id + '&iframe=1')
        .done(function(data) {
            $('#modalContent').html(data);
        })
        .fail(function() {
            $('#modalContent').html('<div class="alert alert-danger">Erro ao carregar detalhes.</div>');
        });
}

function aprovarRequisicao(id) {
    if (confirm('Tem certeza que deseja APROVAR esta requisição?')) {
        const form = $('<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?iframe=1"><input type="hidden" name="action" value="approve"><input type="hidden" name="request_id" value="' + id + '"></form>');
        $('body').append(form);
        form.submit();
    }
}

function rejeitarRequisicao(id) {
    if (confirm('Tem certeza que deseja REJEITAR esta requisição?')) {
        const form = $('<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?iframe=1"><input type="hidden" name="action" value="reject"><input type="hidden" name="request_id" value="' + id + '"></form>');
        $('body').append(form);
        form.submit();
    }
}

function editarStatus(id) {
    $('#status_request_id').val(id);
    $('#modalStatus').modal('show');
}

// Ajustar altura do iframe quando carregado
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
</script>
<?php } ?>

<?php
$conexao->close();
?>