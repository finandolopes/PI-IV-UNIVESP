<!-- Main Footer -->
<footer class='main-footer'>
    <!-- To the right -->
    <div class='float-right d-none d-sm-inline'>
        Versão 2.0 - Sistema CONFINTER
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; <?php echo date('Y'); ?> <a href='https://www.confinter.com.br' target='_blank'>CONFINTER</a>.</strong>
    Todos os direitos reservados.
    
</footer>

<!-- Control Sidebar (opcional) -->
<aside class='control-sidebar control-sidebar-dark'>
    <!-- Control sidebar content goes here -->
    <div class='p-3'>
        <h5>Configurações Rápidas</h5>
        <div class='mb-3'>
            <label for='theme-select'>Tema:</label>
            <select class='form-control form-control-sm' id='theme-select'>
                <option value='light'>Claro</option>
                <option value='dark' selected>Escuro</option>
            </select>
        </div>
        <div class='mb-3'>
            <div class='form-check'>
                <input class='form-check-input' type='checkbox' id='sidebar-collapsed' checked>
                <label class='form-check-label' for='sidebar-collapsed'>
                    Sidebar Recolhido
                </label>
            </div>
        </div>
        <div class='mb-3'>
            <div class='form-check'>
                <input class='form-check-input' type='checkbox' id='fixed-navbar'>
                <label class='form-check-label' for='fixed-navbar'>
                    Navbar Fixo
                </label>
            </div>
        </div>
        <hr>
        <h6>Status do Sistema</h6>
        <div class='mb-2'>
            <span class='badge badge-success'>Banco Online</span>
            <span class='badge badge-success'>Servidor Online</span>
        </div>
        <small class='text-muted'>Última atualização: <?php echo date('d/m/Y H:i'); ?></small>
    </div>
</aside>
<!-- /.control-sidebar -->

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- overlayScrollbars -->
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@1.13.1/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(function() {
        // Inicializar overlayScrollbars no body
        $('body').overlayScrollbars({
            className: 'os-theme-light',
            scrollbars: { autoHide: 'leave' }
        });

        // Corrigir tooltips do Bootstrap
        $('[data-toggle="tooltip"]').tooltip();

        // Funções para iframe
        window.adjustIframeHeight = function() {
            if (window.parent && window.parent.adjustIframeHeight) {
                window.parent.adjustIframeHeight();
            }
        };

        // Ajustar altura quando o conteúdo carregar
        $(window).on('load', function() {
            setTimeout(window.adjustIframeHeight, 100);
        });

        // Ajustar altura quando o conteúdo mudar (útil para conteúdo dinâmico)
        $(document).on('DOMSubtreeModified', function() {
            setTimeout(window.adjustIframeHeight, 50);
        });
    });
</script>
