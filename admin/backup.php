<?php
session_start();
require_once '../php/conexao.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
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

// Configurações do banco de dados para backup
$db_config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'dbname' => 'confinter'
];

// Processar ações
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['backup'])) {
        // Criar backup
        $backup_dir = 'backups/';
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }

        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backup_dir . $filename;

        // Comando mysqldump
        $command = "mysqldump --user={$db_config['user']} --password={$db_config['pass']} --host={$db_config['host']} {$db_config['dbname']} > \"{$filepath}\"";
        exec($command, $output, $return_var);

        if ($return_var === 0) {
            $message = 'Backup criado com sucesso: ' . $filename;
            $message_type = 'success';
        } else {
            $message = 'Erro ao criar backup. Verifique as permissões do sistema.';
            $message_type = 'danger';
        }
    }
}

// Buscar backups existentes
$backups = [];
$backup_dir = 'backups/';
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backups[] = [
                'name' => $file,
                'date' => date('d/m/Y H:i:s', filemtime($backup_dir . $file)),
                'size' => filesize($backup_dir . $file)
            ];
        }
    }
    rsort($backups); // Ordenar por data decrescente
}

// Estatísticas para os cards
$total_backups = count($backups);
$backups_hoje = 0;
$espaco_usado = 0;
$ultimo_backup = 'Nunca';

if (!empty($backups)) {
    $ultimo_backup = date('d/m/Y H:i', filemtime($backup_dir . $backups[0]['name']));

    // Contar backups de hoje
    $hoje = date('Y-m-d');
    foreach ($backups as $backup) {
        $backup_date = date('Y-m-d', filemtime($backup_dir . $backup['name']));
        if ($backup_date === $hoje) {
            $backups_hoje++;
        }
        $espaco_usado += $backup['size'];
    }
}

// Download de backup
if (isset($_GET['download'])) {
    $filename = basename($_GET['download']);
    $filepath = $backup_dir . $filename;

    if (file_exists($filepath) && pathinfo($filepath, PATHINFO_EXTENSION) === 'sql') {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        $message = 'Arquivo de backup não encontrado.';
        $message_type = 'danger';
    }
}
?>

<?php if (!$is_iframe): ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CONFINTER - Backup do Sistema</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <style>
        .backup-card {
            transition: all 0.3s ease;
        }
        .backup-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-online { background-color: #28a745; }
        .status-offline { background-color: #dc3545; }
        .status-warning { background-color: #ffc107; }
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
                        <i class="fas fa-database mr-2"></i>
                        Backup do Sistema
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
                        <li class="breadcrumb-item active">Backup</li>
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
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $total_backups; ?></h3>
                            <p>Total de Backups</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-archive"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $backups_hoje; ?></h3>
                            <p>Backups Hoje</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $ultimo_backup; ?></h3>
                            <p>Último Backup</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="small-box-footer">
                            <?php echo $ultimo_backup !== 'Nunca' ? 'Atualizado' : 'Nunca feito'; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo number_format($espaco_usado / 1024 / 1024, 1); ?> MB</h3>
                            <p>Espaço Usado</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-hdd"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensagens -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> mr-2"></i><?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Criar Backup -->
                <div class="col-md-6">
                    <div class="card backup-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-plus-circle mr-1"></i>
                                Criar Novo Backup
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <i class="fas fa-database fa-3x text-primary mb-3"></i>
                                <h5>Criar Backup do Banco de Dados</h5>
                                <p class="text-muted">Clique no botão abaixo para criar um backup completo do banco de dados. O arquivo será salvo automaticamente na pasta <code>backups/</code> do sistema.</p>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Informação:</strong> O backup inclui todas as tabelas, dados e estrutura do banco de dados.
                            </div>

                            <form method="post" class="text-center">
                                <button type="submit" name="backup" class="btn btn-primary btn-lg">
                                    <i class="fas fa-download mr-2"></i>Criar Backup Agora
                                </button>
                            </form>
                        </div>
                        <div class="card-footer">
                            <small class="text-muted">
                                <i class="fas fa-clock mr-1"></i>
                                Último backup: <?php echo $ultimo_backup; ?>
                            </small>
                        </div>
                    </div>

                    <!-- Status do Sistema -->
                    <div class="card backup-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-server mr-1"></i>
                                Status do Sistema
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <span class="status-indicator status-online"></span>
                                        Banco de Dados
                                    </span>
                                    <span class="badge badge-success">Online</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <span class="status-indicator status-online"></span>
                                        Pasta de Backups
                                    </span>
                                    <span class="badge badge-success">Acessível</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fab fa-php mr-2"></i>
                                        PHP Version
                                    </span>
                                    <span class="badge badge-info"><?php echo PHP_VERSION; ?></span>
                                </div>
                            </div>
                            <div class="mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fas fa-code-branch mr-2"></i>
                                        Versão do Sistema
                                    </span>
                                    <span class="badge badge-secondary">v2.0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Backups -->
                <div class="col-md-6">
                    <div class="card backup-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-1"></i>
                                Backups Disponíveis
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-info"><?php echo $total_backups; ?> backups</span>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <?php if (empty($backups)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-database fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Nenhum backup encontrado</h5>
                                    <p class="text-muted">Crie seu primeiro backup usando o botão ao lado</p>
                                </div>
                            <?php else: ?>
                                <table class="table table-hover text-nowrap mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0">
                                                <i class="fas fa-file-code mr-1"></i>Nome do Arquivo
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-calendar mr-1"></i>Data de Criação
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-weight mr-1"></i>Tamanho
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-cogs mr-1"></i>Ações
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($backups as $backup): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($backup['name']); ?></strong>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?php echo $backup['date']; ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light"><?php echo number_format($backup['size'] / 1024, 1); ?> KB</span>
                                                </td>
                                                <td>
                                                    <a href="?download=<?php echo urlencode($backup['name']); ?>"
                                                       class="btn btn-sm btn-success" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($backups)): ?>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <small class="text-muted">
                                            <i class="fas fa-archive mr-1"></i>
                                            Total: <?php echo $total_backups; ?> backups
                                        </small>
                                    </div>
                                    <div class="col-sm-6 text-right">
                                        <button type="button" class="btn btn-warning btn-sm" onclick="limparBackups()">
                                            <i class="fas fa-trash mr-1"></i>Limpar Antigos
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Informações de Backup -->
                    <div class="card backup-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle mr-1"></i>
                                Informações sobre Backup
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Atenção:</strong> Os backups são salvos localmente no servidor. Recomenda-se fazer download regularmente e armazenar em local seguro.
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Local dos Backups</span>
                                            <span class="info-box-number">/admin/backups/</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text">Formato</span>
                                            <span class="info-box-number">SQL</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <h6><i class="fas fa-shield-alt mr-1"></i>Recomendações de Segurança:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success mr-1"></i> Faça backups regulares</li>
                                    <li><i class="fas fa-check text-success mr-1"></i> Teste a restauração periodicamente</li>
                                    <li><i class="fas fa-check text-success mr-1"></i> Armazene backups em local seguro</li>
                                    <li><i class="fas fa-check text-success mr-1"></i> Mantenha múltiplas cópias</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include 'footer.php'; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
function limparBackups() {
    if (confirm('Tem certeza que deseja excluir todos os backups antigos? Esta ação não pode ser desfeita.')) {
        // Implementar limpeza de backups se necessário
        alert('Funcionalidade em desenvolvimento');
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

</body>
</html>
<?php endif; ?>