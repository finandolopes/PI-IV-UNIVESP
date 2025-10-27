/**
 * CONFINTER Admin Panel - Custom Scripts
 * AdminLTE Style with Bootstrap 5
 */

$(document).ready(function() {
    // Initialize AdminLTE
    if (typeof AdminLTE !== 'undefined') {
        AdminLTE.init();
    }

    // Initialize DataTables
    if ($.fn.DataTable) {
        $('.datatable').DataTable({
            "language": {
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "zeroRecords": "Nenhum registro encontrado",
                "info": "Mostrando página _PAGE_ de _PAGES_",
                "infoEmpty": "Nenhum registro disponível",
                "infoFiltered": "(filtrado de _MAX_ registros no total)",
                "search": "Buscar:",
                "paginate": {
                    "first": "Primeiro",
                    "last": "Último",
                    "next": "Próximo",
                    "previous": "Anterior"
                }
            },
            "responsive": true,
            "autoWidth": false
        });
    }

    // Initialize tooltips
    if ($.fn.tooltip) {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    // Initialize popovers
    if ($.fn.popover) {
        $('[data-bs-toggle="popover"]').popover();
    }

    // SweetAlert2 confirmations
    $(document).on('click', '.btn-delete, .delete-btn', function(e) {
        e.preventDefault();
        const href = $(this).attr('href') || $(this).data('href');
        const title = $(this).data('title') || 'Tem certeza?';
        const text = $(this).data('text') || 'Esta ação não pode ser desfeita!';

        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                if (href) {
                    window.location.href = href;
                } else {
                    // Submit form if inside one
                    $(this).closest('form').submit();
                }
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Form validation
    $('form').on('submit', function(e) {
        const requiredFields = $(this).find('[required]');
        let isValid = true;

        requiredFields.each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Campos obrigatórios',
                text: 'Por favor, preencha todos os campos obrigatórios.'
            });
        }
    });

    // Clear validation on input
    $('input, textarea, select').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // Loading states for buttons
    $(document).on('click', '.btn-loading', function() {
        const $btn = $(this);
        const originalText = $btn.html();

        $btn.prop('disabled', true);
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Processando...');

        // Re-enable after 10 seconds as fallback
        setTimeout(function() {
            $btn.prop('disabled', false);
            $btn.html(originalText);
        }, 10000);
    });

    // File input preview
    $('.file-input').on('change', function() {
        const file = this.files[0];
        const $preview = $(this).data('preview');

        if (file && $preview) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $($preview).attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Password toggle
    $(document).on('click', '.password-toggle', function() {
        const $input = $(this).siblings('input[type="password"], input[type="text"]');
        const $icon = $(this).find('i');

        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Counter animation
    if ($.fn.counterUp) {
        $('.counter').counterUp({
            delay: 10,
            time: 1000
        });
    }

    // Responsive table wrapper
    $('.table-responsive .table').each(function() {
        if (!$(this).closest('.table-responsive').length) {
            $(this).wrap('<div class="table-responsive"></div>');
        }
    });

    // Sidebar treeview toggle
    $('.sidebar-menu .has-treeview > a').on('click', function(e) {
        e.preventDefault();
        const $parent = $(this).parent();
        const $submenu = $parent.find('.nav-treeview').first();

        if ($parent.hasClass('menu-open')) {
            $submenu.slideUp(300);
            $parent.removeClass('menu-open');
        } else {
            // Close other open menus
            $('.sidebar-menu .menu-open').removeClass('menu-open').find('.nav-treeview').slideUp(300);

            $submenu.slideDown(300);
            $parent.addClass('menu-open');
        }
    });

    // Card collapse
    $(document).on('click', '[data-widget="collapse"]', function() {
        const $card = $(this).closest('.card');
        const $body = $card.find('.card-body, .card-footer');

        if ($card.hasClass('collapsed-card')) {
            $body.slideDown(300);
            $card.removeClass('collapsed-card');
            $(this).find('i').removeClass('fa-plus').addClass('fa-minus');
        } else {
            $body.slideUp(300);
            $card.addClass('collapsed-card');
            $(this).find('i').removeClass('fa-minus').addClass('fa-plus');
        }
    });

    // Card remove
    $(document).on('click', '[data-widget="remove"]', function() {
        $(this).closest('.card').slideUp(300, function() {
            $(this).remove();
        });
    });

    // Initialize Feather icons if available
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Print functionality
    $(document).on('click', '.btn-print', function() {
        window.print();
    });

    // Export to Excel (basic implementation)
    $(document).on('click', '.btn-export-excel', function() {
        const table = $(this).data('table') || '.table';
        const filename = $(this).data('filename') || 'export';

        // Simple CSV export
        let csv = [];
        $(table + ' thead tr').each(function() {
            let row = [];
            $(this).find('th').each(function() {
                row.push('"' + $(this).text().trim() + '"');
            });
            csv.push(row.join(','));
        });

        $(table + ' tbody tr').each(function() {
            let row = [];
            $(this).find('td').each(function() {
                row.push('"' + $(this).text().trim() + '"');
            });
            csv.push(row.join(','));
        });

        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');

        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    });

    // Auto-resize textareas
    $('textarea').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Initialize on page load
    console.log('CONFINTER Admin Panel initialized successfully!');

    // Update current time in footer
    function updateCurrentTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('pt-BR', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        $('#current-time').text(timeString);
    }

    // Update time every second
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);

    // Add loading class to body initially
    $('body').addClass('loading');
    $(window).on('load', function() {
        $('body').removeClass('loading');
        // Add fade-in effect
        $('.content').addClass('fade-in');
    });

    // Loading states for forms
    $('form').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        if (submitBtn.length) {
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processando...');
        }
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(event) {
        const target = $($(this).attr('href'));
        if (target.length) {
            event.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });

    // Enhanced keyboard navigation
    $(document).on('keydown', function(e) {
        // ESC to close modals
        if (e.keyCode === 27) {
            $('.modal').modal('hide');
        }
    });

    // Print functionality
    $('.btn-print').on('click', function() {
        window.print();
    });

    // Export functionality (basic implementation)
    $('.btn-export').on('click', function() {
        const table = $(this).closest('.card').find('table');
        if (table.length) {
            const csv = [];
            table.find('tr').each(function() {
                const row = [];
                $(this).find('th, td').each(function() {
                    row.push('"' + $(this).text().trim() + '"');
                });
                csv.push(row.join(','));
            });
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'export.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
    });

    console.log('CONFINTER Admin Panel initialized successfully!');
});