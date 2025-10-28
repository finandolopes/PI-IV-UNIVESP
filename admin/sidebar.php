<?php
// Função para verificar se a página atual está ativa
function isActive($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return $current_page === $page ? 'active' : '';
}

// Buscar estatísticas rápidas para o sidebar
$sidebar_stats = [
    'pending_requests' => 0,
    'pending_testimonials' => 0,
    'total_users' => 0
];

if (isset($conexao)) {
    $req_query = 'SELECT COUNT(*) as total FROM requisicoes WHERE status = \'pendente\'';
    $req_result = mysqli_query($conexao, $req_query);
    if ($req_result) {
        $row = mysqli_fetch_assoc($req_result);
        $sidebar_stats['pending_requests'] = $row ? $row['total'] : 0;
    }
    
    $test_query = 'SELECT COUNT(*) as total FROM depoimentos WHERE aprovado = 0';
    $test_result = mysqli_query($conexao, $test_query);
    if ($test_result) {
        $row = mysqli_fetch_assoc($test_result);
        $sidebar_stats['pending_testimonials'] = $row ? $row['total'] : 0;
    }
    
    $users_query = 'SELECT COUNT(*) as total FROM adm';
    $users_result = mysqli_query($conexao, $users_query);
    if ($users_result) {
        $row = mysqli_fetch_assoc($users_result);
        $sidebar_stats['total_users'] = $row ? $row['total'] : 0;
    }
    
    $reset_query = 'SELECT COUNT(*) as total FROM reset_senha_solicitacoes WHERE status = \'pendente\'';
    $reset_result = mysqli_query($conexao, $reset_query);
    if ($reset_result) {
        $row = mysqli_fetch_assoc($reset_result);
        $sidebar_stats['reset_pendentes'] = $row ? $row['total'] : 0;
    }
}
?>
<!-- Main Sidebar Container -->
<aside class='main-sidebar sidebar-dark-primary elevation-4'>
    <!-- Brand Logo -->
    <a href='admin.php' class='brand-link'>
        <span class='brand-text font-weight-light'>CONFINTER</span>
    </a>

    <!-- Sidebar -->
    <div class='sidebar'>
        <!-- Sidebar Menu -->
        <nav class='mt-2'>
            <ul class='nav nav-pills nav-sidebar flex-column' data-widget='treeview' role='menu' data-accordion='false'>
                <!-- Dashboard -->
                <li class='nav-item'>
                    <a href='#' onclick="window.backToDashboard ? window.backToDashboard() : window.location.reload()" class='nav-link <?php echo isActive('admin.php'); ?>' data-toggle='tooltip' data-placement='right' title='Dashboard - Visão geral do sistema'>
                        <i class='nav-icon fas fa-tachometer-alt'></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Gestão de Usuários -->
                <li class='nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['listarusuario.php', 'novousuario.php', 'perfil.php', 'reset_senha_admin.php']) ? 'menu-open' : ''; ?>'>
                    <a href='#' class='nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['listarusuario.php', 'novousuario.php', 'perfil.php', 'reset_senha_admin.php']) ? 'active' : ''; ?>' data-toggle='tooltip' data-placement='right' title='Gestão de Usuários'>
                        <i class='nav-icon fas fa-users'></i>
                        <p>
                            Usuários
                            <i class='right fas fa-angle-left'></i>
                        </p>
                    </a>
                    <ul class='nav nav-treeview'>
                        <li class='nav-item'>
                            <a href='#' onclick="loadInIframe('listarusuario.php', 'Listar Usuários')" class='nav-link <?php echo isActive('listarusuario.php'); ?>' data-toggle='tooltip' data-placement='right' title='Listar todos os usuários'>
                                <i class='far fa-circle nav-icon'></i>
                                <p>Listar Usuários</p>
                            </a>
                        </li>
                        <li class='nav-item'>
                            <a href='#' onclick="loadInIframe('novousuario.php', 'Novo Usuário')" class='nav-link <?php echo isActive('novousuario.php'); ?>' data-toggle='tooltip' data-placement='right' title='Cadastrar novo usuário'>
                                <i class='far fa-circle nav-icon'></i>
                                <p>Novo Usuário</p>
                            </a>
                        </li>
                        <li class='nav-item'>
                            <a href='#' onclick="loadInIframe('perfil.php', 'Meu Perfil')" class='nav-link <?php echo isActive('perfil.php'); ?>' data-toggle='tooltip' data-placement='right' title='Editar perfil pessoal'>
                                <i class='far fa-circle nav-icon'></i>
                                <p>Meu Perfil</p>
                            </a>
                        </li>
                        <li class='nav-item'>
                            <a href='#' onclick="loadInIframe('reset_senha_admin.php', 'Gerenciar Reset de Senha')" class='nav-link <?php echo isActive('reset_senha_admin.php'); ?>' data-toggle='tooltip' data-placement='right' title='Gerenciar solicitações de reset de senha'>
                                <i class='far fa-circle nav-icon'></i>
                                <p>
                                    Gerenciar Reset
                                    <?php if ($sidebar_stats['reset_pendentes'] > 0): ?>
                                        <span class='right badge badge-danger'><?php echo $sidebar_stats['reset_pendentes']; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Gestão de Clientes -->
                <li class='nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['buscar_empresa.php', 'clientedit.php']) ? 'menu-open' : ''; ?>'>
                    <a href='#' class='nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['buscar_empresa.php', 'clientedit.php']) ? 'active' : ''; ?>' data-toggle='tooltip' data-placement='right' title='Gestão de Clientes'>
                        <i class='nav-icon fas fa-building'></i>
                        <p>
                            Clientes
                            <i class='right fas fa-angle-left'></i>
                        </p>
                    </a>
                    <ul class='nav nav-treeview'>
                        <li class='nav-item'>
                            <a href='#' onclick="loadInIframe('buscar_empresa.php', 'Buscar Clientes')" class='nav-link <?php echo isActive('buscar_empresa.php'); ?>' data-toggle='tooltip' data-placement='right' title='Buscar e listar clientes'>
                                <i class='far fa-circle nav-icon'></i>
                                <p>Buscar Clientes</p>
                            </a>
                        </li>
                        <li class='nav-item'>
                            <a href='#' onclick="loadInIframe('clientedit.php', 'Editar Cliente')" class='nav-link <?php echo isActive('clientedit.php'); ?>' data-toggle='tooltip' data-placement='right' title='Editar dados do cliente'>
                                <i class='far fa-circle nav-icon'></i>
                                <p>Editar Cliente</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Requisições -->
                <li class='nav-item'>
                    <a href='#' onclick="loadInIframe('requisicoes.php', 'Requisições')" class='nav-link <?php echo isActive('requisicoes.php'); ?>' data-toggle='tooltip' data-placement='right' title='Gerenciar requisições de crédito'>
                        <i class='nav-icon fas fa-clipboard-list'></i>
                        <p>
                            Requisições
                            <?php if ($sidebar_stats['pending_requests'] > 0): ?>
                                <span class='right badge badge-danger'><?php echo $sidebar_stats['pending_requests']; ?></span>
                            <?php endif; ?>
                        </p>
                    </a>
                </li>

                <!-- Depoimentos -->
                <li class='nav-item'>
                    <a href='#' onclick="loadInIframe('mod_depoimentos.php', 'Depoimentos')" class='nav-link <?php echo isActive('mod_depoimentos.php'); ?>' data-toggle='tooltip' data-placement='right' title='Moderar depoimentos dos clientes'>
                        <i class='nav-icon fas fa-comments'></i>
                        <p>
                            Depoimentos
                            <?php if ($sidebar_stats['pending_testimonials'] > 0): ?>
                                <span class='right badge badge-warning'><?php echo $sidebar_stats['pending_testimonials']; ?></span>
                            <?php endif; ?>
                        </p>
                    </a>
                </li>

                <!-- Mídia -->
                <li class='nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['upload_imagens.php', 'galeria.php']) ? 'menu-open' : ''; ?>'>
                    <a href='#' class='nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['upload_imagens.php', 'galeria.php']) ? 'active' : ''; ?>' data-toggle='tooltip' data-placement='right' title='Gerenciamento de mídia e imagens'>
                        <i class='nav-icon fas fa-images'></i>
                        <p>
                            Mídia
                            <i class='right fas fa-angle-left'></i>
                        </p>
                    </a>
                    <ul class='nav nav-treeview'>
                        <li class='nav-item'>
                            <a href='#' onclick="loadInIframe('upload_imagens.php', 'Upload de Imagens')" class='nav-link <?php echo isActive('upload_imagens.php'); ?>' data-toggle='tooltip' data-placement='right' title='Fazer upload de novas imagens'>
                                <i class='far fa-circle nav-icon'></i>
                                <p>Upload de Imagens</p>
                            </a>
                        </li>
                        <li class='nav-item'>
                            <a href='#' onclick="loadInIframe('galeria.php', 'Galeria')" class='nav-link <?php echo isActive('galeria.php'); ?>' data-toggle='tooltip' data-placement='right' title='Visualizar galeria de imagens'>
                                <i class='far fa-circle nav-icon'></i>
                                <p>Galeria</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Relatórios e Analytics -->
                <li class='nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['contador.php', 'relatorios.php']) ? 'menu-open' : ''; ?>'>
                    <a href='#' class='nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['contador.php', 'relatorios.php']) ? 'active' : ''; ?>' data-toggle='tooltip' data-placement='right' title='Relatórios e estatísticas do sistema'>
                        <i class='nav-icon fas fa-chart-bar'></i>
                        <p>
                            Relatórios
                            <i class='right fas fa-angle-left'></i>
                        </p>
                    </a>
                    <ul class='nav nav-treeview'>
                        <li class='nav-item'>
                            <a href='#' onclick="loadInIframe('contador.php', 'Estatísticas')" class='nav-link <?php echo isActive('contador.php'); ?>' data-toggle='tooltip' data-placement='right' title='Visualizar estatísticas de acesso'>
                                <i class='far fa-circle nav-icon'></i>
                                <p>Estatísticas</p>
                            </a>
                        </li>
                        <li class='nav-item'>
                            <a href='#' onclick="loadInIframe('relatorios.php', 'Relatórios')" class='nav-link <?php echo isActive('relatorios.php'); ?>' data-toggle='tooltip' data-placement='right' title='Relatórios detalhados do sistema'>
                                <i class='far fa-circle nav-icon'></i>
                                <p>Relatórios Detalhados</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Configurações -->
                <li class='nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['configuracoes.php', 'backup.php']) ? 'menu-open' : ''; ?>'>
                    <a href='#' class='nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['configuracoes.php', 'backup.php']) ? 'active' : ''; ?>' data-toggle='tooltip' data-placement='right' title='Configurações do sistema'>
                        <i class='nav-icon fas fa-cogs'></i>
                        <p>
                            Sistema
                            <i class='right fas fa-angle-left'></i>
                        </p>
                    </a>
                    <ul class='nav nav-treeview'>
                        <li class='nav-item'>
                            <a href='#' onclick="loadInIframe('configuracoes.php', 'Configurações')" class='nav-link <?php echo isActive('configuracoes.php'); ?>' data-toggle='tooltip' data-placement='right' title='Configurações gerais do sistema'>
                                <i class='far fa-circle nav-icon'></i>
                                <p>Configurações</p>
                            </a>
                        </li>
                        <li class='nav-item'>
                            <a href='#' onclick="loadInIframe('backup.php', 'Backup')" class='nav-link <?php echo isActive('backup.php'); ?>' data-toggle='tooltip' data-placement='right' title='Fazer backup do sistema'>
                                <i class='far fa-circle nav-icon'></i>
                                <p>Backup</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Separator -->
                <li class='nav-header'>INFORMAÇÕES RÁPIDAS</li>

                <!-- Quick Stats -->
                <li class='nav-item'>
                    <a href='#' class='nav-link' data-toggle='tooltip' data-placement='right' title='Total de usuários cadastrados no sistema'>
                        <i class='nav-icon fas fa-info-circle'></i>
                        <p>
                            Usuários Cadastrados
                            <span class='right badge badge-info'><?php echo $sidebar_stats['total_users']; ?></span>
                        </p>
                    </a>
                </li>

                <li class='nav-item'>
                    <a href='#' onclick="loadInIframe('requisicoes.php', 'Requisições')" class='nav-link' data-toggle='tooltip' data-placement='right' title='Requisições aguardando análise'>
                        <i class='nav-icon fas fa-clock'></i>
                        <p>
                            Requisições Pendentes
                            <span class='right badge badge-warning'><?php echo $sidebar_stats['pending_requests']; ?></span>
                        </p>
                    </a>
                </li>

                <li class='nav-item'>
                    <a href='#' onclick="loadInIframe('mod_depoimentos.php', 'Depoimentos')" class='nav-link' data-toggle='tooltip' data-placement='right' title='Depoimentos aguardando moderação'>
                        <i class='nav-icon fas fa-comment-dots'></i>
                        <p>
                            Depoimentos Pendentes
                            <span class='right badge badge-secondary'><?php echo $sidebar_stats['pending_testimonials']; ?></span>
                        </p>
                    </a>
                </li>

                <!-- Link de Teste do Iframe -->
                
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

<script>
// Funções globais para o sistema de iframe - definidas no sidebar para estarem disponíveis quando o sidebar for carregado
window.loadInIframe = function(url, title) {
    console.log('loadInIframe function called with:', url, title);
    var iframe = document.getElementById('content-iframe');
    var titleElement = document.getElementById('iframe-title');
    var dashboardContent = document.getElementById('dashboard-content');
    var iframeContainer = document.getElementById('iframe-container');

    if (!iframe || !titleElement || !dashboardContent || !iframeContainer) {
        console.error('Iframe elements not found!');
        alert('Erro: Elementos do iframe não encontrados! Recarregue a página.');
        return;
    }

    console.log('Elements found, proceeding...');

    // Adicionar parâmetro iframe=true se não estiver presente
    if (url.indexOf('?') === -1) {
        url += '?iframe=true';
    } else {
        url += '&iframe=true';
    }

    console.log('Loading URL:', url);

    iframe.src = url;
    titleElement.textContent = title;
    dashboardContent.style.display = 'none';
    iframeContainer.style.display = 'block';

    console.log('Iframe loaded successfully');

    history.pushState({iframe: true, url: url, title: title}, title, '?page=' + encodeURIComponent(url));
};

window.backToDashboard = function() {
    var iframe = document.getElementById('content-iframe');
    var dashboardContent = document.getElementById('dashboard-content');
    var iframeContainer = document.getElementById('iframe-container');

    if (!iframe || !dashboardContent || !iframeContainer) {
        console.error('Iframe elements not found!');
        return;
    }

    iframe.src = '';
    dashboardContent.style.display = 'block';
    iframeContainer.style.display = 'none';

    history.pushState({}, document.title, window.location.pathname);
};

window.adjustIframeHeight = function() {
    var iframe = document.getElementById('content-iframe');
    if (iframe) {
        try {
            // Aguardar um pouco para garantir que o conteúdo do iframe carregou
            setTimeout(function() {
                if (iframe.contentWindow && iframe.contentWindow.document && iframe.contentWindow.document.body) {
                    var body = iframe.contentWindow.document.body;
                    var html = iframe.contentWindow.document.documentElement;

                    // Calcular a altura real do conteúdo
                    var height = Math.max(
                        body.scrollHeight,
                        body.offsetHeight,
                        html.clientHeight,
                        html.scrollHeight,
                        html.offsetHeight
                    );

                    // Adicionar um padding extra para garantir que não haja scroll
                    height += 100;

                    // Definir altura mínima
                    if (height < 600) height = 600;

                    iframe.style.height = height + 'px';
                    console.log('Iframe height adjusted to:', height);
                } else {
                    // Fallback para altura mínima se não conseguir acessar o conteúdo
                    iframe.style.height = '600px';
                }
            }, 500); // Aguardar 500ms para o conteúdo carregar
        } catch (e) {
            console.warn('Could not adjust iframe height:', e);
            iframe.style.height = '600px';
        }
    }
};

$(document).ready(function() {
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Mostrar tooltips apenas quando o sidebar estiver recolhido
    function updateTooltips() {
        var isCollapsed = $('body').hasClass('sidebar-collapse') || $('body').hasClass('sidebar-closed');
        if (isCollapsed) {
            $('[data-toggle="tooltip"]').tooltip('enable');
        } else {
            $('[data-toggle="tooltip"]').tooltip('disable');
        }
    }
    
    // Verificar estado inicial
    updateTooltips();

    // Atualizar quando o sidebar muda de estado
    $(document).on('collapsed.lte.pushmenu expanded.lte.pushmenu', function() {
        updateTooltips();
    });
});
</script>

<script>
$(document).ready(function() {
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Atualizar tooltips quando o sidebar é recolhido/expandido
    $('[data-widget="pushmenu"]').on('click', function() {
        setTimeout(function() {
            $('[data-toggle="tooltip"]').tooltip('dispose').tooltip();
        }, 300);
    });
    
    // Mostrar tooltips apenas quando o sidebar estiver recolhido
    function updateTooltips() {
        var isCollapsed = $('body').hasClass('sidebar-collapse') || $('body').hasClass('sidebar-closed');
        if (isCollapsed) {
            $('[data-toggle="tooltip"]').tooltip('enable');
        } else {
            $('[data-toggle="tooltip"]').tooltip('disable');
        }
    }
    
    // Verificar estado inicial
    updateTooltips();

    // Atualizar quando o sidebar muda de estado
    $(document).on('collapsed.lte.pushmenu expanded.lte.pushmenu', function() {
        updateTooltips();
    });
});
</script>
