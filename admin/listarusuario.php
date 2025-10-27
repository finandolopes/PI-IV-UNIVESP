<?php
session_start();
include_once('../php/conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Verificar se é admin
$user_query = "SELECT perfil FROM adm WHERE usuario = ?";
$stmt = $conexao->prepare($user_query);
$stmt->bind_param("s", $_SESSION['usuario']);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

if (!$user_data || $user_data['perfil'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Detectar se está em iframe
$is_iframe = isset($_GET['iframe']) || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin.php') !== false);

// Buscar usuários
$query = "
    SELECT
        u.*,
        COUNT(r.id_requisicao) as total_requisicoes,
        COUNT(CASE WHEN r.status = 'aprovado' THEN 1 END) as requisicoes_aprovadas,
        COUNT(CASE WHEN r.status = 'reprovado' THEN 1 END) as requisicoes_reprovadas
    FROM adm u
    LEFT JOIN requisicoes r ON u.id_usuario = r.analista_id
    GROUP BY u.id_usuario
    ORDER BY u.data_cadastro DESC
";

$result = $conexao->query($query);
$usuarios = $result->fetch_all(MYSQLI_ASSOC);

// Estatísticas dos usuários
$query_stats = "
    SELECT
        COUNT(*) as total_usuarios,
        SUM(CASE WHEN perfil = 'admin' THEN 1 ELSE 0 END) as admins,
        SUM(CASE WHEN perfil = 'analista' THEN 1 ELSE 0 END) as analistas,
        0 as ativos_hoje
    FROM adm
";
$stats_result = $conexao->query($query_stats);
$stats = $stats_result->fetch_assoc();

// Estatística adicional: total de requisições
$req_total = 0;
$res_req = $conexao->query("SELECT COUNT(*) as total_reqs FROM requisicoes");
if ($res_req) {
    $row_req = $res_req->fetch_assoc();
    $req_total = (int)($row_req['total_reqs'] ?? 0);
}

// Depoimentos pendentes
$query_depo = "SELECT COUNT(*) as pendentes FROM depoimentos WHERE aprovado = 0";
$depo_result = $conexao->query($query_depo);
$depo_data = $depo_result->fetch_assoc();
$depoimentos_pendentes = $depo_data['pendentes'];

// Reset pendentes
$query_reset = "SELECT COUNT(*) as pendentes FROM reset_senha_solicitacoes WHERE status = 'pendente'";
$reset_result = $conexao->query($query_reset);
$reset_data = $reset_result->fetch_assoc();
$reset_pendentes = $reset_data['pendentes'];

if (!$is_iframe) {
    // Versão completa com navbar e sidebar
    include 'navbar.php';
    include 'sidebar.php';
?>
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
<?php } else { ?>
    <!-- Versão Iframe -->
    <!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Gerenciar Usuários - CONFINTER</title>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
        <!-- DataTables (Bootstrap 4) -->
        <link rel='stylesheet' href='https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css'>
        <link rel='stylesheet' href='https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css'>
        <style>
            body { background: #f4f6f9; margin: 0; padding: 20px; }
            .content-wrapper { margin: 0; background: transparent; }
            .card { box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
        </style>
    </head>
    <body>
<?php } ?>

<!-- Content Header -->
<div class='content-header'>
    <div class='container-fluid'>
        <div class='row mb-2'>
            <div class='col-sm-6'>
                <h1 class='m-0'>
                    <i class='fas fa-users mr-2'></i>
                    Gerenciar Usuários
                </h1>
            </div>
            <div class='col-sm-6'>
                <ol class='breadcrumb float-sm-right'>
                    <li class='breadcrumb-item'><a href='admin.php'>Dashboard</a></li>
                    <li class='breadcrumb-item active'>Usuários</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class='content'>
    <div class='container-fluid'>

        <!-- Estatísticas -->
        <div class='row mb-4'>
            <div class='col-lg-3 col-6'>
                <div class='small-box bg-info'>
                    <div class='inner'>
                        <h3><?php echo $stats['total_usuarios']; ?></h3>
                        <p>Total de Usuários</p>
                    </div>
                    <div class='icon'>
                        <i class='fas fa-users'></i>
                    </div>
                </div>
            </div>
            <div class='col-lg-3 col-6'>
                <div class='small-box bg-success'>
                    <div class='inner'>
                        <h3><?php echo $stats['admins']; ?></h3>
                        <p>Administradores</p>
                    </div>
                    <div class='icon'>
                        <i class='fas fa-user-shield'></i>
                    </div>
                </div>
            </div>
            <div class='col-lg-3 col-6'>
                <div class='small-box bg-warning'>
                    <div class='inner'>
                        <h3><?php echo $stats['analistas']; ?></h3>
                        <p>Analistas</p>
                    </div>
                    <div class='icon'>
                        <i class='fas fa-user-tie'></i>
                    </div>
                </div>
            </div>
            <div class='col-lg-3 col-6'>
                <div class='small-box bg-danger'>
                    <div class='inner'>
                        <h3><?php echo $req_total; ?></h3>
                        <p>Total de Requisições</p>
                    </div>
                    <div class='icon'>
                        <i class='fas fa-clipboard-list'></i>
                    </div>
                </div>
            </div>
        </div>

    <!-- Botão Novo Usuário -->
    <div class='row mb-3'>
        <div class='col-12'>
            <a href='novousuario.php' class='btn btn-primary'>
                <i class='fas fa-plus mr-1'></i> Novo Usuário
            </a>
        </div>
    </div>

    <!-- Tabela de Usuários -->
    <div class='card'>
        <div class='card-header'>
            <h3 class='card-title'>Lista de Usuários</h3>
        </div>
        <div class='card-body'>
            <div class='table-responsive'>
                <table id='usuariosTable' class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Usuário</th>
                            <th>Email</th>
                            <th>Perfil</th>
                            <th>Status</th>
                            <th>Total Requisições</th>
                            <th>Data Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo $usuario['id_usuario']; ?></td>
                            <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td>
                                <span class='badge badge-<?php echo $usuario['perfil'] === 'admin' ? 'danger' : 'info'; ?>'>
                                    <?php echo ucfirst($usuario['perfil']); ?>
                                </span>
                            </td>
                            <td>
                                <span class='badge badge-<?php echo (isset($usuario['status']) && $usuario['status'] === 'ativo') ? 'success' : 'secondary'; ?>'>
                                    <?php echo isset($usuario['status']) ? ucfirst($usuario['status']) : 'Ativo'; ?>
                                </span>
                            </td>
                            <td><?php echo $usuario['total_requisicoes']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($usuario['data_cadastro'])); ?></td>
                            <td>
                                <div class='btn-group'>
                                    <button type='button' class='btn btn-info btn-sm' onclick='verUsuario(<?php echo $usuario['id_usuario']; ?>)' title='Ver Detalhes'>
                                        <i class='fas fa-eye'></i>
                                    </button>
                                    <button type='button' class='btn btn-warning btn-sm' onclick='editarUsuario(<?php echo $usuario['id_usuario']; ?>)' title='Editar'>
                                        <i class='fas fa-edit'></i>
                                    </button>
                                    <?php if (isset($usuario['status']) && $usuario['status'] === 'ativo'): ?>
                                    <button type='button' class='btn btn-sm btn-danger' onclick='desativarUsuario(<?php echo $usuario['id_usuario']; ?>)' title='Desativar'>
                                        <i class='fas fa-ban'></i>
                                    </button>
                                    <?php else: ?>
                                    <button type='button' class='btn btn-sm btn-success' onclick='reativarUsuario(<?php echo $usuario['id_usuario']; ?>)' title='Reativar'>
                                        <i class='fas fa-check'></i>
                                    </button>
                                    <?php endif; ?>
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
</section>

<?php if (!$is_iframe): ?>
    </div>
    </section>
</div>
<?php include 'footer.php'; ?>
<?php else: ?>
    <!-- Scripts (iframe) -->
    <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js'></script>

    <!-- DataTables (iframe) -->
    <script src='https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js'></script>
    <script src='https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js'></script>
    <script src='https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js'></script>
    <script src='https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js'></script>
    </body>
    </html>
<?php endif; ?>

<script>
    $(document).ready(function() {
        $('#usuariosTable').DataTable({
            'responsive': true,
            'language': {
                'url': '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
            },
            'order': [[7, 'desc']], // Ordenar por data de cadastro decrescente
            'columnDefs': [
                { 'orderable': false, 'targets': 8 } // Desabilitar ordenação na coluna de ações
            ]
        });
    });

    function verUsuario(id) {
        <?php if ($is_iframe): ?>
        window.parent.loadInIframe('perfil.php?id=' + id, 'Ver Usuário');
        <?php else: ?>
        window.location.href = 'perfil.php?id=' + id;
        <?php endif; ?>
    }

    function editarUsuario(id) {
        <?php if ($is_iframe): ?>
        window.parent.loadInIframe('editusuario.php?id=' + id, 'Editar Usuário');
        <?php else: ?>
        window.location.href = 'editusuario.php?id=' + id;
        <?php endif; ?>
    }

    function desativarUsuario(id) {
        if (confirm('Tem certeza que deseja desativar este usuário?')) {
            $.ajax({
                url: 'processar_usuario.php',
                type: 'POST',
                data: {
                    id_usuario: id,
                    acao: 'desativar'
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Erro: ' + response.message);
                    }
                },
                error: function() {
                    alert('Erro ao processar solicitação.');
                }
            });
        }
    }

    function reativarUsuario(id) {
        if (confirm('Tem certeza que deseja reativar este usuário?')) {
            $.ajax({
                url: 'processar_usuario.php',
                type: 'POST',
                data: {
                    id_usuario: id,
                    acao: 'reativar'
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Erro: ' + response.message);
                    }
                },
                error: function() {
                    alert('Erro ao processar solicitação.');
                }
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