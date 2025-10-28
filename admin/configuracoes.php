<?php
session_start();
include_once('../php/conexao.php');

// Detectar se está em iframe
$is_iframe = isset($_GET['iframe']) || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin.php') !== false);

// Verifica se o usuário está logado
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Estatísticas para os cards
$query_backups_count = "SELECT COUNT(*) as total FROM configuracoes WHERE backup_automatico = 1";
$result_backups_count = mysqli_query($conexao, $query_backups_count);
$backups_ativos = 0;
if ($result_backups_count) {
    $row = mysqli_fetch_assoc($result_backups_count);
    $backups_ativos = $row ? $row['total'] : 0;
}

$query_backups_files = "SELECT COUNT(*) as total FROM configuracoes WHERE id = 1 AND backup_automatico = 1";
$result_backups_files = mysqli_query($conexao, $query_backups_files);
$backups_arquivos = 0;
if ($result_backups_files) {
    $row = mysqli_fetch_assoc($result_backups_files);
    $backups_arquivos = $row ? $row['total'] : 0;
}

$query_manutencao = "SELECT COUNT(*) as total FROM configuracoes WHERE manutencao = 1";
$result_manutencao = mysqli_query($conexao, $query_manutencao);
$modo_manutencao = 0;
if ($result_manutencao) {
    $row = mysqli_fetch_assoc($result_manutencao);
    $modo_manutencao = $row ? $row['total'] : 0;
}

$query_notificacoes = "SELECT COUNT(*) as total FROM configuracoes WHERE email_notificacoes = 1";
$result_notificacoes = mysqli_query($conexao, $query_notificacoes);
$notificacoes_ativas = 0;
if ($result_notificacoes) {
    $row = mysqli_fetch_assoc($result_notificacoes);
    $notificacoes_ativas = $row ? $row['total'] : 0;
}

// Buscar configurações atuais
$configuracoes = [];
$query = "SELECT * FROM configuracoes WHERE id = 1";
$result = mysqli_query($conexao, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $configuracoes = mysqli_fetch_assoc($result);
} else {
    // Configurações padrão
    $configuracoes = [
        'nome_sistema' => 'CONFINTER',
        'email_contato' => 'contato@confinter.com.br',
        'telefone_contato' => '(11) 99999-9999',
        'backup_automatico' => 0,
        'backup_horario' => '02:00',
        'backup_frequencia' => 'diario',
        'manutencao' => 0,
        'email_notificacoes' => 1,
        'limite_requisicoes_dia' => 100,
        'taxa_juros_padrao' => 2.5
    ];
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'salvar_config') {
        $nome_sistema = $_POST['nome_sistema'] ?? '';
        $email_contato = $_POST['email_contato'] ?? '';
        $telefone_contato = $_POST['telefone_contato'] ?? '';
        $backup_automatico = isset($_POST['backup_automatico']) ? 1 : 0;
        $backup_horario = $_POST['backup_horario'] ?? '02:00';
        $backup_frequencia = $_POST['backup_frequencia'] ?? 'diario';
        $manutencao = isset($_POST['manutencao']) ? 1 : 0;
        $email_notificacoes = isset($_POST['email_notificacoes']) ? 1 : 0;
        $limite_requisicoes = (int)($_POST['limite_requisicoes'] ?? 100);
        $taxa_juros = (float)($_POST['taxa_juros'] ?? 2.5);

        // Verificar se já existe configuração
        $check_query = "SELECT id FROM configuracoes WHERE id = 1";
        $check_result = mysqli_query($conexao, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            // Update
            $stmt = $conexao->prepare("UPDATE configuracoes SET nome_sistema=?, email_contato=?, telefone_contato=?, backup_automatico=?, backup_horario=?, backup_frequencia=?, manutencao=?, email_notificacoes=?, limite_requisicoes_dia=?, taxa_juros_padrao=? WHERE id=1");
            $stmt->bind_param("sssisssiddi", $nome_sistema, $email_contato, $telefone_contato, $backup_automatico, $backup_horario, $backup_frequencia, $manutencao, $email_notificacoes, $limite_requisicoes, $taxa_juros);
        } else {
            // Insert
            $stmt = $conexao->prepare("INSERT INTO configuracoes (id, nome_sistema, email_contato, telefone_contato, backup_automatico, backup_horario, backup_frequencia, manutencao, email_notificacoes, limite_requisicoes_dia, taxa_juros_padrao) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssisssidd", $nome_sistema, $email_contato, $telefone_contato, $backup_automatico, $backup_horario, $backup_frequencia, $manutencao, $email_notificacoes, $limite_requisicoes, $taxa_juros);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = 'Configurações salvas com sucesso!';
            // Recarregar configurações
            $query = "SELECT * FROM configuracoes WHERE id = 1";
            $result = mysqli_query($conexao, $query);
            if ($result && mysqli_num_rows($result) > 0) {
                $configuracoes = mysqli_fetch_assoc($result);
            }
        } else {
            $_SESSION['error'] = 'Erro ao salvar configurações: ' . $stmt->error;
        }
        $stmt->close();

    } elseif ($action === 'backup_manual') {
        // Criar backup manual
        $backup_dir = 'backups/';
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }

        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backup_dir . $filename;

        // Comando mysqldump
        $command = "mysqldump --user={$username} --password={$password} --host={$host} {$dbname} > {$filepath}";
        exec($command, $output, $return_var);

        if ($return_var === 0) {
            $_SESSION['success'] = 'Backup criado com sucesso: ' . $filename;
        } else {
            $_SESSION['error'] = 'Erro ao criar backup.';
        }
    } elseif ($action === 'restaurar_backup' && isset($_FILES['backup_file'])) {
        if ($_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
            $backup_file = $_FILES['backup_file']['tmp_name'];

            // Comando mysql para restaurar
            $command = "mysql --user={$username} --password={$password} --host={$host} {$dbname} < {$backup_file}";
            exec($command, $output, $return_var);

            if ($return_var === 0) {
                $_SESSION['success'] = 'Backup restaurado com sucesso!';
            } else {
                $_SESSION['error'] = 'Erro ao restaurar backup.';
            }
        } else {
            $_SESSION['error'] = 'Erro no upload do arquivo de backup.';
        }
    }
}

// Buscar backups existentes
$backups = [];
if (is_dir('backups/')) {
    $files = scandir('backups/');
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backups[] = [
                'nome' => $file,
                'tamanho' => filesize('backups/' . $file),
                'data' => date('d/m/Y H:i:s', filemtime('backups/' . $file))
            ];
        }
    }
    rsort($backups); // Ordenar por data decrescente
}
?>

<?php if (!$is_iframe): ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CONFINTER - Configurações do Sistema</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        .config-card {
            transition: all 0.3s ease;
        }
        .config-card:hover {
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
                        <i class="fas fa-cogs mr-2"></i>
                        Configurações do Sistema
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
                        <li class="breadcrumb-item active">Configurações</li>
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
                            <h3><?php echo $backups_ativos; ?></h3>
                            <p>Backup Automático</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="small-box-footer">
                            <?php echo $backups_ativos > 0 ? 'Ativo' : 'Inativo'; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo count($backups); ?></h3>
                            <p>Backups Disponíveis</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="small-box-footer">
                            &nbsp;
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $modo_manutencao; ?></h3>
                            <p>Modo Manutenção</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="small-box-footer">
                            <?php echo $modo_manutencao > 0 ? 'Ativo' : 'Inativo'; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo $notificacoes_ativas; ?></h3>
                            <p>Notificações Email</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="small-box-footer">
                            <?php echo $notificacoes_ativas > 0 ? 'Ativas' : 'Inativas'; ?>
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

            <div class="row">
                <!-- Configurações Gerais -->
                <div class="col-md-8">
                    <div class="card config-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-sliders-h mr-1"></i>
                                Configurações Gerais
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <form action="" method="post">
                            <input type="hidden" name="action" value="salvar_config">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nome_sistema">
                                                <i class="fas fa-building mr-1"></i>Nome do Sistema
                                            </label>
                                            <input type="text" class="form-control" id="nome_sistema" name="nome_sistema"
                                                   value="<?php echo htmlspecialchars($configuracoes['nome_sistema'] ?? 'CONFINTER'); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email_contato">
                                                <i class="fas fa-envelope mr-1"></i>Email de Contato
                                            </label>
                                            <input type="email" class="form-control" id="email_contato" name="email_contato"
                                                   value="<?php echo htmlspecialchars($configuracoes['email_contato'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="telefone_contato">
                                                <i class="fas fa-phone mr-1"></i>Telefone de Contato
                                            </label>
                                            <input type="text" class="form-control" id="telefone_contato" name="telefone_contato"
                                                   value="<?php echo htmlspecialchars($configuracoes['telefone_contato'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="limite_requisicoes">
                                                <i class="fas fa-chart-line mr-1"></i>Limite de Requisições/Dia
                                            </label>
                                            <input type="number" class="form-control" id="limite_requisicoes" name="limite_requisicoes"
                                                   value="<?php echo $configuracoes['limite_requisicoes_dia'] ?? 100; ?>" min="1">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="taxa_juros">
                                        <i class="fas fa-percent mr-1"></i>Taxa de Juros Padrão (%)
                                    </label>
                                    <input type="number" class="form-control" id="taxa_juros" name="taxa_juros" step="0.01"
                                           value="<?php echo $configuracoes['taxa_juros_padrao'] ?? 2.5; ?>" min="0">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="email_notificacoes" name="email_notificacoes"
                                                   <?php echo ($configuracoes['email_notificacoes'] ?? 1) ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="email_notificacoes">
                                                <i class="fas fa-bell mr-1"></i>Notificações por email
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="manutencao" name="manutencao"
                                                   <?php echo ($configuracoes['manutencao'] ?? 0) ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="manutencao">
                                                <i class="fas fa-wrench mr-1"></i>Modo de manutenção
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i>Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Configurações de Backup -->
                    <div class="card config-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-database mr-1"></i>
                                Configurações de Backup
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <form action="" method="post">
                            <input type="hidden" name="action" value="salvar_config">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="backup_automatico" name="backup_automatico"
                                                   <?php echo ($configuracoes['backup_automatico'] ?? 0) ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="backup_automatico">
                                                <i class="fas fa-clock mr-1"></i>Backup automático
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="backup_horario">
                                                <i class="fas fa-clock mr-1"></i>Horário
                                            </label>
                                            <input type="time" class="form-control" id="backup_horario" name="backup_horario"
                                                   value="<?php echo $configuracoes['backup_horario'] ?? '02:00'; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="backup_frequencia">
                                                <i class="fas fa-calendar-alt mr-1"></i>Frequência
                                            </label>
                                            <select class="form-control" id="backup_frequencia" name="backup_frequencia">
                                                <option value="diario" <?php echo ($configuracoes['backup_frequencia'] ?? 'diario') === 'diario' ? 'selected' : ''; ?>>Diário</option>
                                                <option value="semanal" <?php echo ($configuracoes['backup_frequencia'] ?? 'diario') === 'semanal' ? 'selected' : ''; ?>>Semanal</option>
                                                <option value="mensal" <?php echo ($configuracoes['backup_frequencia'] ?? 'diario') === 'mensal' ? 'selected' : ''; ?>>Mensal</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i>Salvar Configurações de Backup
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Ações de Backup e Status -->
                <div class="col-md-4">
                    <div class="card config-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-shield-alt mr-1"></i>
                                Backup e Restauração
                            </h3>
                        </div>
                        <div class="card-body">
                            <form action="" method="post" class="mb-3">
                                <input type="hidden" name="action" value="backup_manual">
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-download mr-2"></i>Criar Backup Manual
                                </button>
                            </form>

                            <form action="" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="restaurar_backup">
                                <div class="form-group">
                                    <label for="backup_file">
                                        <i class="fas fa-file-upload mr-1"></i>Arquivo de Backup (.sql)
                                    </label>
                                    <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".sql" required>
                                </div>
                                <button type="submit" class="btn btn-warning btn-block" onclick="return confirm('Tem certeza que deseja restaurar o backup? Isso irá sobrescrever os dados atuais.')">
                                    <i class="fas fa-upload mr-2"></i>Restaurar Backup
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Backups Recentes -->
                    <div class="card config-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history mr-1"></i>
                                Backups Recentes
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-info"><?php echo count($backups); ?> backups</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($backups)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-database fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Nenhum backup encontrado</p>
                                    <small class="text-muted">Crie seu primeiro backup acima</small>
                                </div>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach (array_slice($backups, 0, 5) as $backup): ?>
                                        <li class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-fill">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-calendar mr-1"></i><?php echo $backup['data']; ?>
                                                    </small>
                                                    <strong class="d-block"><?php echo htmlspecialchars($backup['nome']); ?></strong>
                                                    <small class="text-muted">
                                                        <i class="fas fa-hdd mr-1"></i><?php echo number_format($backup['tamanho'] / 1024, 1); ?> KB
                                                    </small>
                                                </div>
                                                <a href="backups/<?php echo urlencode($backup['nome']); ?>" class="btn btn-sm btn-outline-primary ml-2" download title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Status do Sistema -->
                    <div class="card config-card">
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
                                        Servidor Web
                                    </span>
                                    <span class="badge badge-success">Online</span>
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
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fas fa-clock mr-2"></i>
                                        Último Backup
                                    </span>
                                    <small class="text-muted">
                                        <?php echo !empty($backups) ? date('d/m/Y H:i', filemtime('backups/' . $backups[0]['nome'])) : 'Nunca'; ?>
                                    </small>
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

</body>
</html>
<?php endif; ?>

<?php
$conexao->close();
?>