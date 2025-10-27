<?php
// Buscar notificações pendentes
$pending_requests = 0;
$pending_testimonials = 0;

if (isset($conexao)) {
    $req_query = 'SELECT COUNT(*) as total FROM requisicoes WHERE status = \'pendente\'';
    $req_result = mysqli_query($conexao, $req_query);
    if ($req_result) {
        $row = mysqli_fetch_assoc($req_result);
        $pending_requests = $row ? $row['total'] : 0;
    }
    
    $test_query = 'SELECT COUNT(*) as total FROM depoimentos WHERE aprovado = 0';
    $test_result = mysqli_query($conexao, $test_query);
    if ($test_result) {
        $row = mysqli_fetch_assoc($test_result);
        $pending_testimonials = $row ? $row['total'] : 0;
    }
}

// Buscar dados do usuário logado
$user_name = $_SESSION['username'] ?? 'Usuário';
$user_email = '';
$user_avatar = '';

if (isset($conexao) && isset($_SESSION['user_id'])) {
    $user_query = 'SELECT nome, email FROM adm WHERE id_usuario = ?';
    $stmt = $conexao->prepare($user_query);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $user_result = $stmt->get_result();
    if ($user_data = $user_result->fetch_assoc()) {
        $user_name = $user_data['nome'] ?? $user_name;
        $user_email = $user_data['email'] ?? '';
    }
    $stmt->close();
}
?>
<!-- Navbar -->
<nav class='main-header navbar navbar-expand navbar-white navbar-light'>
    <!-- Left navbar links -->
    <ul class='navbar-nav'>
        <li class='nav-item'>
            <a class='nav-link' data-widget='pushmenu' href='#' role='button'>
                <i class='fas fa-bars'></i>
            </a>
        </li>
        <li class='nav-item d-none d-lg-inline-block'>
            <a href='admin.php' class='nav-link'>
                <i class='fas fa-tachometer-alt mr-1'></i>Dashboard
            </a>
        </li>
        <li class='nav-item d-none d-lg-inline-block'>
            <a href='../index.php' target='_blank' class='nav-link'>
                <i class='fas fa-external-link-alt mr-1'></i>Ver Site
            </a>
        </li>
    </ul>

    <!-- Center - SEARCH FORM -->
    <div class='d-flex justify-content-center flex-fill'>
        <form class='form-inline' action='buscar_empresa.php' method='get' id='searchForm' style='max-width: 400px; width: 100%;'>
            <div class='input-group input-group-sm'>
                <input class='form-control form-control-navbar' type='search' name='search' placeholder='Buscar clientes...' aria-label='Search' id='searchInput'>
                <div class='input-group-append'>
                    <button class='btn btn-navbar' type='submit'>
                        <i class='fas fa-search'></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Right navbar links -->
    <ul class='navbar-nav'>
        <!-- Quick Actions -->
        <li class='nav-item d-none d-lg-inline-block'>
            <a href='requisicoes.php' class='nav-link'>
                <i class='fas fa-clipboard-list mr-1'></i>Requisições
            </a>
        </li>
        <li class='nav-item d-none d-lg-inline-block'>
            <a href='mod_depoimentos.php' class='nav-link'>
                <i class='fas fa-comments mr-1'></i>Depoimentos
            </a>
        </li>

        <!-- Messages Dropdown Menu -->
        <li class='nav-item dropdown'>
            <a class='nav-link' data-toggle='dropdown' href='#' id='messagesDropdown'>
                <i class='far fa-comments'></i>
                <span class='badge badge-danger navbar-badge'>2</span>
            </a>
            <div class='dropdown-menu dropdown-menu-lg dropdown-menu-right'>
                <a href='#' class='dropdown-item'>
                    <div class='media'>
                        <div class='media-body'>
                            <h3 class='dropdown-item-title'>Sistema CONFINTER</h3>
                            <p class='text-sm'>Bem-vindo ao painel administrativo!</p>
                            <p class='text-sm text-muted'><i class='far fa-clock mr-1'></i> Agora</p>
                        </div>
                    </div>
                </a>
                <a href='#' class='dropdown-item'>
                    <div class='media'>
                        <div class='media-body'>
                            <h3 class='dropdown-item-title'>Atualização</h3>
                            <p class='text-sm'>Sistema atualizado para versão 2.0</p>
                            <p class='text-sm text-muted'><i class='far fa-clock mr-1'></i> 2 horas atrás</p>
                        </div>
                    </div>
                </a>
                <div class='dropdown-divider'></div>
                <a href='#' class='dropdown-item dropdown-footer'>Ver todas as mensagens</a>
            </div>
        </li>

        <!-- Notifications Dropdown Menu -->
        <li class='nav-item dropdown'>
            <a class='nav-link' data-toggle='dropdown' href='#' id='notificationsDropdown'>
                <i class='far fa-bell'></i>
                <?php if (($pending_requests + $pending_testimonials) > 0): ?>
                    <span class='badge badge-warning navbar-badge'><?php echo $pending_requests + $pending_testimonials; ?></span>
                <?php endif; ?>
            </a>
            <div class='dropdown-menu dropdown-menu-lg dropdown-menu-right'>
                <span class='dropdown-item dropdown-header'>
                    <?php echo $pending_requests + $pending_testimonials; ?> notificações pendentes
                </span>
                <div class='dropdown-divider'></div>
                <?php if ($pending_requests > 0): ?>
                    <a href='requisicoes.php' class='dropdown-item'>
                        <i class='fas fa-clipboard-list mr-2'></i> <?php echo $pending_requests; ?> requisições pendentes
                        <span class='float-right text-muted text-sm'>Verificar</span>
                    </a>
                <?php endif; ?>
                <?php if ($pending_testimonials > 0): ?>
                    <a href='mod_depoimentos.php' class='dropdown-item'>
                        <i class='fas fa-comments mr-2'></i> <?php echo $pending_testimonials; ?> depoimentos para moderar
                        <span class='float-right text-muted text-sm'>Moderar</span>
                    </a>
                <?php endif; ?>
                <div class='dropdown-divider'></div>
                <a href='#' class='dropdown-item dropdown-footer'>Ver todas as notificações</a>
            </div>
        </li>

        <!-- User Account Menu -->
        <li class='nav-item dropdown user-menu'>
            <a href='#' class='nav-link dropdown-toggle' data-toggle='dropdown' id='userDropdown'>
                <img src='../assets/img/avatar/avatar1.svg' class='user-image img-circle elevation-2' alt='User Image' style='width: 32px; height: 32px; object-fit: cover;'>
                <span class='d-none d-md-inline ml-2'><?php echo htmlspecialchars($user_name); ?></span>
            </a>
            <ul class='dropdown-menu dropdown-menu-lg dropdown-menu-right'>
                <!-- User image -->
                <li class='user-header bg-primary' style='background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);'>
                    <img src='../assets/img/avatar/avatar1.svg' class='img-circle elevation-2' alt='User Image' style='width: 60px; height: 60px; object-fit: cover; border: 3px solid rgba(255,255,255,0.3);'>
                    <p class='mb-1'>
                        <strong><?php echo htmlspecialchars($user_name); ?></strong>
                        <small class='text-light'><?php echo htmlspecialchars($user_email); ?></small>
                    </p>
                </li>
                <!-- Menu Body -->
                <li class='user-body'>
                    <div class='row'>
                        <div class='col-6 text-center'>
                            <a href='perfil.php' class='btn btn-default btn-sm'>
                                <i class='fas fa-user mr-1'></i>Perfil
                            </a>
                        </div>
                        <div class='col-6 text-center'>
                            <a href='reset_senha.php' class='btn btn-default btn-sm'>
                                <i class='fas fa-key mr-1'></i>Senha
                            </a>
                        </div>
                    </div>
                </li>
                <!-- Menu Footer-->
                <li class='user-footer'>
                    <a href='listarusuario.php' class='btn btn-primary btn-sm'>
                        <i class='fas fa-users mr-1'></i>Gerenciar Usuários
                    </a>
                    <a href='logout.php' class='btn btn-danger btn-sm float-right'>
                        <i class='fas fa-sign-out-alt mr-1'></i>Sair
                    </a>
                </li>
            </ul>
        </li>

        <!-- Fullscreen -->
        <li class='nav-item'>
            <a class='nav-link' data-widget='fullscreen' href='#' role='button' title='Tela Cheia'>
                <i class='fas fa-expand-arrows-alt'></i>
            </a>
        </li>
    </ul>
</nav>
<!-- /.navbar -->

<style>
/* Navbar Custom Styles */
.main-header.navbar {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-bottom: 1px solid #dee2e6;
    min-height: 55px;
}

.navbar-nav .nav-link {
    padding: 0.5rem 0.75rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.navbar-nav .nav-link:hover {
    background-color: rgba(0,123,255,0.1);
    border-radius: 4px;
}

.navbar-nav .nav-link i {
    margin-right: 0.25rem;
}

.user-image {
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.dropdown-menu-lg {
    min-width: 300px;
}

.user-header {
    padding: 1rem;
    text-align: center;
}

.user-body .btn {
    margin: 0.25rem;
    border-radius: 4px;
    font-size: 0.875rem;
}

.user-footer .btn {
    border-radius: 4px;
    font-size: 0.875rem;
}

/* Centralizar busca */
.d-flex.justify-content-center.flex-fill {
    flex: 1;
    justify-content: center;
    margin: 0 1rem;
}

.d-flex.justify-content-center.flex-fill .input-group {
    max-width: 350px;
    width: 100%;
}

/* Ajustes para mobile */
@media (max-width: 991.98px) {
    .navbar-nav .d-none.d-lg-inline-block {
        display: none !important;
    }
    
    .d-flex.justify-content-center.flex-fill {
        display: none !important;
    }
    
    .navbar-nav.ml-auto {
        margin-left: auto !important;
    }
}

/* Melhorar aparência dos dropdowns */
.dropdown-menu {
    border: none;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border-radius: 8px;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    transition: background-color 0.3s ease;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}
</style>

<script>
$(document).ready(function() {
    // Inicializar dropdowns
    $('.dropdown-toggle').dropdown();
    
    // Funcionalidade de busca
    $('#searchForm').on('submit', function(e) {
        var searchTerm = $('#searchInput').val().trim();
        if (searchTerm.length < 2) {
            e.preventDefault();
            alert('Digite pelo menos 2 caracteres para buscar.');
            return false;
        }
    });
    
    // Auto-complete para busca (opcional)
    $('#searchInput').on('keyup', function() {
        var query = $(this).val();
        if (query.length >= 2) {
            // Aqui poderia implementar busca em tempo real
            console.log('Buscando: ' + query);
        }
    });
    
    // Marcar notificações como lidas ao clicar
    $('.dropdown-item[href]').on('click', function() {
        var $badge = $(this).closest('.dropdown').find('.navbar-badge');
        if ($badge.length) {
            $badge.fadeOut();
        }
    });
});
</script>
