<?php
session_start();
include_once('../php/conexao.php');

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION['usuario']) || (($_SESSION['perfil'] ?? 'admin') !== 'admin')) {
    header('Location: login.php');
    exit();
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'create':
                $nome = trim($_POST['nome']);
                $email = trim($_POST['email']);
                $username = trim($_POST['username']);
                $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                $tipo = $_POST['tipo'];
                $status = $_POST['status'];
                
                $stmt = $conexao->prepare('INSERT INTO usuarios (nome, email, username, senha, tipo, status, data_cadastro) VALUES (?, ?, ?, ?, ?, ?, NOW())');
                $stmt->bind_param('ssssss', $nome, $email, $username, $senha, $tipo, $status);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Usuário criado com sucesso!';
                } else {
                    $_SESSION['error'] = 'Erro ao criar usuário: ' . $stmt->error;
                }
                $stmt->close();
                break;
                
            case 'update':
                $id = $_POST['id'];
                $nome = trim($_POST['nome']);
                $email = trim($_POST['email']);
                $tipo = $_POST['tipo'];
                $status = $_POST['status'];
                
                $sql = 'UPDATE usuarios SET nome = ?, email = ?, tipo = ?, status = ?';
                $params = [$nome, $email, $tipo, $status];
                $types = 'ssss';
                
                if (!empty($_POST['senha'])) {
                    $sql .= ', senha = ?';
                    $params[] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                    $types .= 's';
                }
                
                $sql .= ' WHERE id_usuario = ?';
                $params[] = $id;
                $types .= 'i';
                
                $stmt = $conexao->prepare($sql);
                $stmt->bind_param($types, ...$params);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Usuário atualizado com sucesso!';
                } else {
                    $_SESSION['error'] = 'Erro ao atualizar usuário: ' . $stmt->error;
                }
                $stmt->close();
                break;
                
            case 'delete':
                $id = $_POST['id'];
                
                // Verificar se não está tentando deletar a si mesmo
                if ($id == $_SESSION['user_id']) {
                    $_SESSION['error'] = 'Você não pode deletar sua própria conta!';
                    break;
                }
                
                $stmt = $conexao->prepare('DELETE FROM usuarios WHERE id_usuario = ?');
                $stmt->bind_param('i', $id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Usuário deletado com sucesso!';
                } else {
                    $_SESSION['error'] = 'Erro ao deletar usuário: ' . $stmt->error;
                }
                $stmt->close();
                break;
                
            case 'reset_password':
                $id = $_POST['id'];
                $nova_senha = password_hash('123456', PASSWORD_DEFAULT); // Senha padrão
                
                $stmt = $conexao->prepare('UPDATE usuarios SET senha = ? WHERE id_usuario = ?');
                $stmt->bind_param('si', $nova_senha, $id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Senha resetada para "123456" com sucesso!';
                } else {
                    $_SESSION['error'] = 'Erro ao resetar senha: ' . $stmt->error;
                }
                $stmt->close();
                break;
        }
        
        header('Location: usuarios.php');
        exit();
    }
}

// Filtros
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tipo_filter = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'todos';

// Query base
$query = 'SELECT * FROM usuarios WHERE 1=1';
$params = [];
$types = '';

// Aplicar filtros
if (!empty($search)) {
    $query .= ' AND (nome LIKE ? OR email LIKE ? OR username LIKE ?)';
    $search_param = '%' . $search . '%';
    $params = array_fill(0, 3, $search_param);
    $types .= str_repeat('s', 3);
}

if ($tipo_filter !== 'todos') {
    $query .= ' AND tipo = ?';
    $params[] = $tipo_filter;
    $types .= 's';
}

if ($status_filter !== 'todos') {
    $query .= ' AND status = ?';
    $params[] = $status_filter;
    $types .= 's';
}

$query .= ' ORDER BY data_cadastro DESC';

// Executar query
if (!empty($params)) {
    $stmt = $conexao->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = mysqli_query($conexao, $query);
}

$usuarios = [];
while ($row = mysqli_fetch_assoc($result)) {
    $usuarios[] = $row;
}

// Estatísticas
$stats = [
    'total' => count($usuarios),
    'admin' => 0,
    'analista' => 0,
    'ativo' => 0,
    'inativo' => 0
];

foreach ($usuarios as $user) {
    $stats[$user['tipo']]++;
    $stats[$user['status']]++;
}
?>
<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Gerenciar Usuários - CONFINTER</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback'>
    <!-- Font Awesome -->
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
    <!-- AdminLTE CSS -->
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css'>
    <!-- DataTables -->
    <link rel='stylesheet' href='https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css'>
    <!-- AdminLTE -->
    <link rel='stylesheet' href='assets/css/adminlte.css'>
    <!-- Custom Admin CSS -->
    <link rel='stylesheet' href='assets/css/custom-admin.css'>
</head>
<body class='hold-transition sidebar-mini layout-fixed'>
<div class='wrapper'>

<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>

<!-- Content Wrapper -->
<div class='content-wrapper'>
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
            <!-- Alertas -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <i class='fas fa-check-circle mr-2'></i>
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <i class='fas fa-exclamation-triangle mr-2'></i>
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                </div>
            <?php endif; ?>

            <!-- Cards de Estatísticas -->
            <div class='row mb-4'>
                <div class='col-lg-3 col-6'>
                    <div class='small-box bg-info'>
                        <div class='inner'>
                            <h3><?php echo number_format($stats['total']); ?></h3>
                            <p>Total de Usuários</p>
                        </div>
                        <div class='icon'>
                            <i class='fas fa-users'></i>
                        </div>
                    </div>
                </div>

                <div class='col-lg-3 col-6'>
                    <div class='small-box bg-warning'>
                        <div class='inner'>
                            <h3><?php echo number_format($stats['admin']); ?></h3>
                            <p>Administradores</p>
                        </div>
                        <div class='icon'>
                            <i class='fas fa-crown'></i>
                        </div>
                    </div>
                </div>

                <div class='col-lg-3 col-6'>
                    <div class='small-box bg-primary'>
                        <div class='inner'>
                            <h3><?php echo number_format($stats['analista']); ?></h3>
                            <p>Analistas</p>
                        </div>
                        <div class='icon'>
                            <i class='fas fa-user-tie'></i>
                        </div>
                    </div>
                </div>

                <div class='col-lg-3 col-6'>
                    <div class='small-box bg-success'>
                        <div class='inner'>
                            <h3><?php echo number_format($stats['ativo']); ?></h3>
                            <p>Usuários Ativos</p>
                        </div>
                        <div class='icon'>
                            <i class='fas fa-user-check'></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botão Novo Usuário -->
            <div class='row mb-3'>
                <div class='col-12'>
                    <button class='btn btn-primary' data-toggle='modal' data-target='#modalNovoUsuario'>
                        <i class='fas fa-plus mr-1'></i> Novo Usuário
                    </button>
                </div>
            </div>

            <!-- Filtros -->
            <div class='card'>
                <div class='card-header'>
                    <h3 class='card-title'>
                        <i class='fas fa-filter mr-1'></i>
                        Filtros e Busca
                    </h3>
                </div>
                <div class='card-body'>
                    <form method='get' action=''>
                        <div class='row'>
                            <div class='col-md-4'>
                                <div class='form-group'>
                                    <label for='search'>Buscar:</label>
                                    <input type='text' class='form-control' id='search' name='search' 
                                           value='<?php echo htmlspecialchars($search); ?>' 
                                           placeholder='Nome, email ou username...'>
                                </div>
                            </div>
                            <div class='col-md-3'>
                                <div class='form-group'>
                                    <label for='tipo'>Tipo:</label>
                                    <select class='form-control' id='tipo' name='tipo'>
                                        <option value='todos' <?php echo $tipo_filter === 'todos' ? 'selected' : ''; ?>>Todos</option>
                                        <option value='admin' <?php echo $tipo_filter === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                        <option value='analista' <?php echo $tipo_filter === 'analista' ? 'selected' : ''; ?>>Analista</option>
                                    </select>
                                </div>
                            </div>
                            <div class='col-md-3'>
                                <div class='form-group'>
                                    <label for='status'>Status:</label>
                                    <select class='form-control' id='status' name='status'>
                                        <option value='todos' <?php echo $status_filter === 'todos' ? 'selected' : ''; ?>>Todos</option>
                                        <option value='ativo' <?php echo $status_filter === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                        <option value='inativo' <?php echo $status_filter === 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                                    </select>
                                </div>
                            </div>
                            <div class='col-md-2'>
                                <div class='form-group'>
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type='submit' class='btn btn-primary btn-block'>
                                            <i class='fas fa-search'></i> Filtrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabela de Usuários -->
            <div class='card'>
                <div class='card-header'>
                    <h3 class='card-title'>
                        <i class='fas fa-table mr-1'></i>
                        Usuários (<?php echo count($usuarios); ?>)
                    </h3>
                </div>
                <div class='card-body table-responsive p-0'>
                    <table id='usuariosTable' class='table table-hover text-nowrap'>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Data Cadastro</th>
                                <th>Último Login</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $user): ?>
                                <tr>
                                    <td><?php echo $user['id_usuario']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['nome']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php 
                                        $tipo_class = $user['tipo'] === 'admin' ? 'badge-warning' : 'badge-primary';
                                        $tipo_text = $user['tipo'] === 'admin' ? 'Administrador' : 'Analista';
                                        ?>
                                        <span class='badge <?php echo $tipo_class; ?>'><?php echo $tipo_text; ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        $status_class = $user['status'] === 'ativo' ? 'badge-success' : 'badge-secondary';
                                        $status_text = $user['status'] === 'ativo' ? 'Ativo' : 'Inativo';
                                        ?>
                                        <span class='badge <?php echo $status_class; ?>'><?php echo $status_text; ?></span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($user['data_cadastro'])); ?></td>
                                    <td>
                                        <?php if ($user['ultimo_login']): ?>
                                            <?php echo date('d/m/Y H:i', strtotime($user['ultimo_login'])); ?>
                                        <?php else: ?>
                                            <span class='text-muted'>Nunca</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class='btn-group'>
                                            <button class='btn btn-sm btn-info' onclick='editarUsuario(<?php echo $user['id_usuario']; ?>)' title='Editar'>
                                                <i class='fas fa-edit'></i>
                                            </button>
                                            <button class='btn btn-sm btn-warning' onclick='resetarSenha(<?php echo $user['id_usuario']; ?>)' title='Resetar Senha'>
                                                <i class='fas fa-key'></i>
                                            </button>
                                            <?php if ($user['id_usuario'] != $_SESSION['user_id']): ?>
                                                <button class='btn btn-sm btn-danger' onclick='deletarUsuario(<?php echo $user['id_usuario']; ?>, "<?php echo htmlspecialchars($user['nome']); ?>")' title='Deletar'>
                                                    <i class='fas fa-trash'></i>
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
    </section>
</div>

<?php include 'footer.php'; ?>

<!-- Modal Novo Usuário -->
<div class='modal fade' id='modalNovoUsuario' tabindex='-1'>
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'>Novo Usuário</h5>
                <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
            </div>
            <form method='post' action=''>
                <div class='modal-body'>
                    <input type='hidden' name='action' value='create'>
                    
                    <div class='mb-3'>
                        <label for='nome' class='form-label'>Nome Completo:</label>
                        <input type='text' class='form-control' name='nome' required>
                    </div>
                    
                    <div class='mb-3'>
                        <label for='email' class='form-label'>Email:</label>
                        <input type='email' class='form-control' name='email' required>
                    </div>
                    
                    <div class='mb-3'>
                        <label for='username' class='form-label'>Username:</label>
                        <input type='text' class='form-control' name='username' required>
                    </div>
                    
                    <div class='mb-3'>
                        <label for='senha' class='form-label'>Senha:</label>
                        <input type='password' class='form-control' name='senha' required>
                    </div>
                    
                    <div class='mb-3'>
                        <label for='tipo' class='form-label'>Tipo:</label>
                        <select class='form-control' name='tipo' required>
                            <option value='analista'>Analista</option>
                            <option value='admin'>Administrador</option>
                        </select>
                    </div>
                    
                    <div class='mb-3'>
                        <label for='status' class='form-label'>Status:</label>
                        <select class='form-control' name='status' required>
                            <option value='ativo'>Ativo</option>
                            <option value='inativo'>Inativo</option>
                        </select>
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancelar</button>
                    <button type='submit' class='btn btn-primary'>Criar Usuário</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Usuário -->
<div class='modal fade' id='modalEditarUsuario' tabindex='-1'>
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'>Editar Usuário</h5>
                <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
            </div>
            <form method='post' action=''>
                <div class='modal-body'>
                    <input type='hidden' name='action' value='update'>
                    <input type='hidden' name='id' id='edit_id'>
                    
                    <div class='mb-3'>
                        <label for='edit_nome' class='form-label'>Nome Completo:</label>
                        <input type='text' class='form-control' name='nome' id='edit_nome' required>
                    </div>
                    
                    <div class='mb-3'>
                        <label for='edit_email' class='form-label'>Email:</label>
                        <input type='email' class='form-control' name='email' id='edit_email' required>
                    </div>
                    
                    <div class='mb-3'>
                        <label for='edit_senha' class='form-label'>Nova Senha (deixe em branco para manter):</label>
                        <input type='password' class='form-control' name='senha' id='edit_senha'>
                    </div>
                    
                    <div class='mb-3'>
                        <label for='edit_tipo' class='form-label'>Tipo:</label>
                        <select class='form-control' name='tipo' id='edit_tipo' required>
                            <option value='analista'>Analista</option>
                            <option value='admin'>Administrador</option>
                        </select>
                    </div>
                    
                    <div class='mb-3'>
                        <label for='edit_status' class='form-label'>Status:</label>
                        <select class='form-control' name='status' id='edit_status' required>
                            <option value='ativo'>Ativo</option>
                            <option value='inativo'>Inativo</option>
                        </select>
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancelar</button>
                    <button type='submit' class='btn btn-primary'>Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js'></script>
<script src='assets/js/adminlte.js'></script>
<script src='https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js'></script>
<script src='https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js'></script>

<script>
$(document).ready(function() {
    $('#usuariosTable').DataTable({
        'language': {
            'url': '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
        },
        'pageLength': 25,
        'responsive': true,
        'order': [[0, 'desc']]
    });
});

function editarUsuario(id) {
    // Buscar dados do usuário via AJAX
    $.get('get_usuario.php?id=' + id)
        .done(function(data) {
            var user = JSON.parse(data);
            $('#edit_id').val(user.id_usuario);
            $('#edit_nome').val(user.nome);
            $('#edit_email').val(user.email);
            $('#edit_tipo').val(user.tipo);
            $('#edit_status').val(user.status);
            $('#edit_senha').val('');
            $('#modalEditarUsuario').modal('show');
        })
        .fail(function() {
            alert('Erro ao carregar dados do usuário.');
        });
}

function resetarSenha(id) {
    if (confirm('Tem certeza que deseja resetar a senha deste usuário para "123456"?')) {
        var form = document.createElement('form');
        form.method = 'post';
        form.action = '';
        
        var inputAction = document.createElement('input');
        inputAction.type = 'hidden';
        inputAction.name = 'action';
        inputAction.value = 'reset_password';
        form.appendChild(inputAction);
        
        var inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id';
        inputId.value = id;
        form.appendChild(inputId);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function deletarUsuario(id, nome) {
    if (confirm('Tem certeza que deseja deletar o usuário "' + nome + '"? Esta ação não pode ser desfeita.')) {
        var form = document.createElement('form');
        form.method = 'post';
        form.action = '';
        
        var inputAction = document.createElement('input');
        inputAction.type = 'hidden';
        inputAction.name = 'action';
        inputAction.value = 'delete';
        form.appendChild(inputAction);
        
        var inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id';
        inputId.value = id;
        form.appendChild(inputId);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

</body>
</html>
<?php
$conexao->close();
?>
