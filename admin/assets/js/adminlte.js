/**
 * AdminLTE 3 Professional Theme - Complete JavaScript Implementation
 * Based on AdminLTE 3.2.0
 */

(function($) {
  'use strict';

  // AdminLTE Global Object
  var AdminLTE = {};

  // Configuration
  AdminLTE.options = {
    animationSpeed: 500,
    sidebarToggleSelector: "[data-widget='sidebar']",
    sidebarPushMenu: true,
    sidebarSlimScroll: true,
    sidebarExpandOnHover: false,
    enableBoxRefresh: true,
    enableBSToppltip: true,
    enableFastclick: false,
    enableControlSidebar: true,
    enableBoxWidget: true,
    enableFnImg: true,
    enableExpandOnHover: false,
    enableHoverSidebar: false,
    sidebarMenuSelector: "[data-widget='treeview']",
    controlSidebarOptions: {
      toggleBtnSelector: "[data-toggle='control-sidebar']",
      selector: ".control-sidebar",
      slide: true
    },
    navbarMenuSlimscroll: true,
    navbarMenuSlimscrollWidth: "3px",
    footerOptions: {
      fixed: false
    },
    boxWidgetOptions: {
      boxWidgetIcons: {
        collapse: 'fa-minus',
        open: 'fa-plus',
        remove: 'fa-times'
      },
      boxWidgetSelectors: {
        remove: '[data-widget="remove"]',
        collapse: '[data-widget="collapse"]'
      }
    },
    directChat: {
      enable: true,
      contactToggleSelector: '[data-widget="chat-pane-toggle"]'
    },
    colors: {
      lightBlue: "#3c8dbc",
      red: "#f56954",
      green: "#00a65a",
      aqua: "#00c0ef",
      yellow: "#f39c12",
      blue: "#0073b7",
      navy: "#001F3F",
      teal: "#39CCCC",
      olive: "#3D9970",
      lime: "#01FF70",
      orange: "#FF851B",
      fuchsia: "#F012BE",
      purple: "#8E24AA",
      maroon: "#D81B60",
      black: "#222222",
      gray: "#d2d6de"
    },
    screenSizes: {
      xs: 480,
      sm: 768,
      md: 992,
      lg: 1200
    }
  };

  // Initialize AdminLTE
  AdminLTE.init = function() {
    this.initSidebar();
    this.initNavbar();
    this.initBoxWidget();
    this.initControlSidebar();
    this.initDirectChat();
    this.initTodoList();
    this.initTreeview();
    this.initPushMenu();
    this.initCardWidget();
    this.initExpandableTable();
    this.initTextArea();
    this.initSidebarSearch();
    this.initLayoutFix();

    // Initialize tooltips if enabled
    if (this.options.enableBSToppltip) {
      $('[data-toggle="tooltip"]').tooltip();
    }

    // Initialize popovers
    $('[data-toggle="popover"]').popover();

    // Initialize fastclick if enabled
    if (this.options.enableFastclick && typeof FastClick !== 'undefined') {
      FastClick.attach(document.body);
    }
  };

  // Sidebar functionality
  AdminLTE.initSidebar = function() {
    var self = this;

    // Sidebar toggle
    $(document).on('click', this.options.sidebarToggleSelector, function(e) {
      e.preventDefault();
      self.toggleSidebar();
    });

    // Sidebar menu item click
    $('.sidebar .nav-link').on('click', function(e) {
      var $this = $(this);
      var $parent = $this.parent();
      var $submenu = $parent.find('.nav-treeview').first();

      if ($submenu.length > 0) {
        e.preventDefault();

        if ($parent.hasClass('menu-open')) {
          $submenu.slideUp(self.options.animationSpeed, function() {
            $parent.removeClass('menu-open');
          });
        } else {
          $submenu.slideDown(self.options.animationSpeed, function() {
            $parent.addClass('menu-open');
          });
        }
      }
    });

    // Sidebar overlay click
    $('.sidebar-overlay').on('click', function() {
      self.closeSidebar();
    });

    // Window resize
    $(window).on('resize', function() {
      self.handleSidebarOnResize();
    });
  };

  // Toggle sidebar
  AdminLTE.toggleSidebar = function() {
    var $body = $('body');
    var $sidebar = $('.main-sidebar');
    var $content = $('.content-wrapper');
    var $overlay = $('.sidebar-overlay');

    if ($(window).width() <= 767) {
      // Mobile
      if ($body.hasClass('sidebar-open')) {
        this.closeSidebar();
      } else {
        this.openSidebar();
      }
    } else {
      // Desktop
      if ($body.hasClass('sidebar-collapse')) {
        this.expandSidebar();
      } else {
        this.collapseSidebar();
      }
    }
  };

  // Open sidebar (mobile)
  AdminLTE.openSidebar = function() {
    $('body').addClass('sidebar-open');
    $('.sidebar-overlay').show();
  };

  // Close sidebar (mobile)
  AdminLTE.closeSidebar = function() {
    $('body').removeClass('sidebar-open');
    $('.sidebar-overlay').hide();
  };

  // Collapse sidebar (desktop)
  AdminLTE.collapseSidebar = function() {
    $('body').addClass('sidebar-collapse');
    this.saveSidebarState('collapse');
  };

  // Expand sidebar (desktop)
  AdminLTE.expandSidebar = function() {
    $('body').removeClass('sidebar-collapse');
    this.saveSidebarState('expand');
  };

  // Handle sidebar on window resize
  AdminLTE.handleSidebarOnResize = function() {
    var $body = $('body');

    if ($(window).width() > 767) {
      if ($body.hasClass('sidebar-open')) {
        this.closeSidebar();
      }
    }
  };

  // Save sidebar state to localStorage
  AdminLTE.saveSidebarState = function(state) {
    if (typeof(Storage) !== 'undefined') {
      localStorage.setItem('AdminLTE.sidebar', state);
    }
  };

  // Load sidebar state from localStorage
  AdminLTE.loadSidebarState = function() {
    if (typeof(Storage) !== 'undefined') {
      var state = localStorage.getItem('AdminLTE.sidebar');
      if (state === 'collapse') {
        this.collapseSidebar();
      }
    }
  };

  // Navbar functionality
  AdminLTE.initNavbar = function() {
    // Navbar search
    $('.navbar-search-open').on('click', function(e) {
      e.preventDefault();
      $('.navbar-search').addClass('open');
      $('.navbar-search input').focus();
    });

    $('.navbar-search-close').on('click', function(e) {
      e.preventDefault();
      $('.navbar-search').removeClass('open');
    });

    // Dropdown menu
    $('.navbar .dropdown').on('shown.bs.dropdown', function() {
      $(this).find('.dropdown-menu').addClass('show');
    });

    $('.navbar .dropdown').on('hidden.bs.dropdown', function() {
      $(this).find('.dropdown-menu').removeClass('show');
    });
  };

  // Box widget functionality
  AdminLTE.initBoxWidget = function() {
    if (!this.options.enableBoxWidget) return;

    var self = this;

    $(document).on('click', this.options.boxWidgetOptions.boxWidgetSelectors.collapse, function(e) {
      e.preventDefault();
      self.collapseBox($(this));
    });

    $(document).on('click', this.options.boxWidgetOptions.boxWidgetSelectors.remove, function(e) {
      e.preventDefault();
      self.removeBox($(this));
    });
  };

  // Collapse box
  AdminLTE.collapseBox = function($element) {
    var $box = $element.parents('.card').first();
    var $box_content = $box.find('.card-body, .card-footer, .table');

    if ($box_content.is(':visible')) {
      $element.children(':first')
        .removeClass(this.options.boxWidgetOptions.boxWidgetIcons.collapse)
        .addClass(this.options.boxWidgetOptions.boxWidgetIcons.open);

      $box_content.slideUp(this.options.animationSpeed, function() {
        $box.addClass('collapsed-card');
      });
    } else {
      $element.children(':first')
        .removeClass(this.options.boxWidgetOptions.boxWidgetIcons.open)
        .addClass(this.options.boxWidgetOptions.boxWidgetIcons.collapse);

      $box_content.slideDown(this.options.animationSpeed, function() {
        $box.removeClass('collapsed-card');
      });
    }
  };

  // Remove box
  AdminLTE.removeBox = function($element) {
    var $box = $element.parents('.card').first();
    $box.slideUp(this.options.animationSpeed, function() {
      $box.remove();
    });
  };

  // Control sidebar functionality
  AdminLTE.initControlSidebar = function() {
    if (!this.options.enableControlSidebar) return;

    var self = this;

    $(document).on('click', this.options.controlSidebarOptions.toggleBtnSelector, function(e) {
      e.preventDefault();
      self.toggleControlSidebar();
    });

    $(document).on('click', '.control-sidebar .nav-link', function() {
      self.closeControlSidebar();
    });
  };

  // Toggle control sidebar
  AdminLTE.toggleControlSidebar = function() {
    var $body = $('body');
    var $controlSidebar = $(this.options.controlSidebarOptions.selector);

    if ($body.hasClass('control-sidebar-open')) {
      this.closeControlSidebar();
    } else {
      this.openControlSidebar();
    }
  };

  // Open control sidebar
  AdminLTE.openControlSidebar = function() {
    $('body').addClass('control-sidebar-open');
  };

  // Close control sidebar
  AdminLTE.closeControlSidebar = function() {
    $('body').removeClass('control-sidebar-open');
  };

  // Direct chat functionality
  AdminLTE.initDirectChat = function() {
    if (!this.options.directChat.enable) return;

    var self = this;

    $(document).on('click', this.options.directChat.contactToggleSelector, function(e) {
      e.preventDefault();
      var $chat = $(this).parents('.direct-chat').first();
      $chat.toggleClass('direct-chat-contacts-open');
    });

    $(document).on('click', '.direct-chat-messages .direct-chat-msg', function() {
      var $this = $(this);
      $this.addClass('direct-chat-primary');
      setTimeout(function() {
        $this.removeClass('direct-chat-primary');
      }, 2000);
    });
  };

  // Todo list functionality
  AdminLTE.initTodoList = function() {
    $(document).on('click', '.todo-list .todo-item input[type="checkbox"]', function() {
      var $this = $(this);
      var $item = $this.parents('.todo-item').first();

      if ($this.prop('checked')) {
        $item.addClass('done');
      } else {
        $item.removeClass('done');
      }
    });
  };

  // Treeview functionality
  AdminLTE.initTreeview = function() {
    $(document).on('click', this.options.sidebarMenuSelector, function(e) {
      e.preventDefault();
      var $this = $(this);
      var $parent = $this.parent();
      var $submenu = $parent.find('.nav-treeview').first();

      if ($submenu.length > 0) {
        if ($parent.hasClass('menu-open')) {
          $submenu.slideUp(this.options.animationSpeed, function() {
            $parent.removeClass('menu-open');
          });
        } else {
          $submenu.slideDown(this.options.animationSpeed, function() {
            $parent.addClass('menu-open');
          });
        }
      }
    });
  };

  // Push menu functionality
  AdminLTE.initPushMenu = function() {
    if (!this.options.sidebarPushMenu) return;

    var self = this;

    $(document).on('click', '.sidebar-toggle', function(e) {
      e.preventDefault();
      self.toggleSidebar();
    });
  };

  // Card widget functionality
  AdminLTE.initCardWidget = function() {
    $(document).on('click', '[data-card-widget="collapse"]', function(e) {
      e.preventDefault();
      var $card = $(this).parents('.card').first();
      var $body = $card.find('.card-body');

      if ($body.is(':visible')) {
        $body.slideUp();
        $card.addClass('collapsed-card');
      } else {
        $body.slideDown();
        $card.removeClass('collapsed-card');
      }
    });

    $(document).on('click', '[data-card-widget="remove"]', function(e) {
      e.preventDefault();
      var $card = $(this).parents('.card').first();
      $card.slideUp(function() {
        $card.remove();
      });
    });

    $(document).on('click', '[data-card-widget="maximize"]', function(e) {
      e.preventDefault();
      var $card = $(this).parents('.card').first();
      $card.toggleClass('card-maximized');
    });
  };

  // Expandable table functionality
  AdminLTE.initExpandableTable = function() {
    $(document).on('click', '.table-expandable .expandable-table-caret', function(e) {
      e.preventDefault();
      var $this = $(this);
      var $tr = $this.parents('tr').first();
      var $detail = $tr.next('.expandable-table-detail');

      if ($detail.is(':visible')) {
        $detail.hide();
        $this.removeClass('fa-minus').addClass('fa-plus');
      } else {
        $detail.show();
        $this.removeClass('fa-plus').addClass('fa-minus');
      }
    });
  };

  // Text area auto resize
  AdminLTE.initTextArea = function() {
    $('textarea[data-auto-resize]').each(function() {
      var $this = $(this);
      $this.css('height', 'auto');
      $this.css('height', $this.prop('scrollHeight') + 'px');

      $this.on('input', function() {
        $this.css('height', 'auto');
        $this.css('height', $this.prop('scrollHeight') + 'px');
      });
    });
  };

  // Sidebar search functionality
  AdminLTE.initSidebarSearch = function() {
    $('.sidebar-search input').on('keyup', function() {
      var searchTerm = $(this).val().toLowerCase();
      $('.sidebar .nav-link').each(function() {
        var $link = $(this);
        var text = $link.text().toLowerCase();

        if (text.indexOf(searchTerm) > -1) {
          $link.show();
          $link.parents('.nav-item').show();
        } else {
          $link.hide();
        }
      });
    });
  };

  // Layout fixes
  AdminLTE.initLayoutFix = function() {
    // Fix for content wrapper height
    var windowHeight = $(window).height();
    var headerHeight = $('.main-header').outerHeight();
    var footerHeight = $('.main-footer').outerHeight() || 0;

    $('.content-wrapper').css('min-height', windowHeight - headerHeight - footerHeight);

    $(window).on('resize', function() {
      var windowHeight = $(window).height();
      var headerHeight = $('.main-header').outerHeight();
      var footerHeight = $('.main-footer').outerHeight() || 0;

      $('.content-wrapper').css('min-height', windowHeight - headerHeight - footerHeight);
    });
  };

  // Utility functions
  AdminLTE.getScreenSize = function() {
    var screenSize = '';
    var windowWidth = $(window).width();

    if (windowWidth >= this.options.screenSizes.lg) {
      screenSize = 'lg';
    } else if (windowWidth >= this.options.screenSizes.md) {
      screenSize = 'md';
    } else if (windowWidth >= this.options.screenSizes.sm) {
      screenSize = 'sm';
    } else {
      screenSize = 'xs';
    }

    return screenSize;
  };

  // Initialize on document ready
  $(document).ready(function() {
    AdminLTE.init();
    AdminLTE.loadSidebarState();
  });

  // Make AdminLTE available globally
  window.AdminLTE = AdminLTE;

})(jQuery);

// SweetAlert2 Integration
function showSuccessAlert(message, title = 'Sucesso!') {
  Swal.fire({
    icon: 'success',
    title: title,
    text: message,
    confirmButtonColor: '#28a745',
    timer: 3000,
    timerProgressBar: true
  });
}

function showErrorAlert(message, title = 'Erro!') {
  Swal.fire({
    icon: 'error',
    title: title,
    text: message,
    confirmButtonColor: '#dc3545'
  });
}

function showWarningAlert(message, title = 'Atenção!') {
  Swal.fire({
    icon: 'warning',
    title: title,
    text: message,
    confirmButtonColor: '#ffc107',
    confirmButtonText: 'OK'
  });
}

function showInfoAlert(message, title = 'Informação') {
  Swal.fire({
    icon: 'info',
    title: title,
    text: message,
    confirmButtonColor: '#17a2b8'
  });
}

function showConfirmAlert(message, title = 'Confirmação', confirmText = 'Sim', cancelText = 'Cancelar') {
  return Swal.fire({
    icon: 'question',
    title: title,
    text: message,
    showCancelButton: true,
    confirmButtonColor: '#007bff',
    cancelButtonColor: '#6c757d',
    confirmButtonText: confirmText,
    cancelButtonText: cancelText
  });
}

// DataTables Integration
function initializeDataTable(selector, options = {}) {
  var defaultOptions = {
    language: {
      url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json'
    },
    responsive: true,
    pageLength: 25,
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
         '<"row"<"col-sm-12"tr>>' +
         '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    initComplete: function() {
      $('.dataTables_filter input').addClass('form-control');
      $('.dataTables_length select').addClass('form-control');
    }
  };

  var mergedOptions = $.extend(true, {}, defaultOptions, options);
  return $(selector).DataTable(mergedOptions);
}

// Chart.js Integration
function createChart(canvasId, type, data, options = {}) {
  var ctx = document.getElementById(canvasId);
  if (!ctx) return null;

  var defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'top',
      },
      tooltip: {
        mode: 'index',
        intersect: false,
      }
    },
    scales: {
      y: {
        beginAtZero: true
      }
    }
  };

  var mergedOptions = $.extend(true, {}, defaultOptions, options);

  return new Chart(ctx, {
    type: type,
    data: data,
    options: mergedOptions
  });
}

// Real-time clock
function updateClock() {
  var now = new Date();
  var timeString = now.toLocaleTimeString('pt-BR', {
    hour12: false,
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
  var dateString = now.toLocaleDateString('pt-BR', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });

  $('.current-time').text(timeString);
  $('.current-date').text(dateString);
}

// Initialize clock if element exists
$(document).ready(function() {
  if ($('.current-time').length > 0 || $('.current-date').length > 0) {
    updateClock();
    setInterval(updateClock, 1000);
  }
});

// Loading overlay
function showLoading(message = 'Carregando...') {
  if (!$('#loading-overlay').length) {
    $('body').append(`
      <div id="loading-overlay" class="d-flex align-items-center justify-content-center"
           style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                  background: rgba(0,0,0,0.5); z-index: 9999;">
        <div class="text-center text-white">
          <div class="spinner-border mb-2" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <div>${message}</div>
        </div>
      </div>
    `);
  }
}

function hideLoading() {
  $('#loading-overlay').fadeOut(function() {
    $(this).remove();
  });
}

// AJAX helper with loading
function ajaxWithLoading(url, options = {}) {
  showLoading();

  var defaultOptions = {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  };

  var mergedOptions = $.extend(true, {}, defaultOptions, options);

  return $.ajax(url, mergedOptions)
    .always(function() {
      hideLoading();
    });
}

// Form validation helper
function validateForm(formSelector) {
  var $form = $(formSelector);
  var isValid = true;

  $form.find('input[required], select[required], textarea[required]').each(function() {
    var $field = $(this);
    var value = $field.val().trim();

    if (!value) {
      $field.addClass('is-invalid');
      isValid = false;
    } else {
      $field.removeClass('is-invalid').addClass('is-valid');
    }
  });

  return isValid;
}

// Auto-hide alerts
$(document).ready(function() {
  $('.alert').each(function() {
    var $alert = $(this);
    if (!$alert.hasClass('alert-permanent')) {
      setTimeout(function() {
        $alert.fadeOut(function() {
          $alert.remove();
        });
      }, 5000);
    }
  });
});

// Print functionality
function printElement(selector) {
  var printContent = $(selector).html();
  var originalContent = $('body').html();

  $('body').html(printContent);
  window.print();
  $('body').html(originalContent);
}

// Export to CSV
function exportToCSV(data, filename) {
  var csvContent = "data:text/csv;charset=utf-8,";

  data.forEach(function(rowArray) {
    var row = rowArray.join(",");
    csvContent += row + "\r\n";
  });

  var encodedUri = encodeURI(csvContent);
  var link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", filename + ".csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

// Copy to clipboard
function copyToClipboard(text) {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(text).then(function() {
      showSuccessAlert('Copiado para a área de transferência!');
    });
  } else {
    // Fallback for older browsers
    var textArea = document.createElement("textarea");
    textArea.value = text;
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
      document.execCommand('copy');
      showSuccessAlert('Copiado para a área de transferência!');
    } catch (err) {
      showErrorAlert('Erro ao copiar para a área de transferência');
    }

    document.body.removeChild(textArea);
  }
}

// Debounce function for search inputs
function debounce(func, wait, immediate) {
  var timeout;
  return function executedFunction() {
    var context = this;
    var args = arguments;
    var later = function() {
      timeout = null;
      if (!immediate) func.apply(context, args);
    };
    var callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) func.apply(context, args);
  };
}

// Initialize search with debounce
$(document).ready(function() {
  $('.search-input').each(function() {
    var $input = $(this);
    var searchFunction = debounce(function() {
      var searchTerm = $input.val();
      // Implement search logic here
      console.log('Searching for:', searchTerm);
    }, 300);

    $input.on('input', searchFunction);
  });
});

  // Initialize Sidebar
  AdminLTE.initSidebar = function() {
    var sidebar = $('.main-sidebar');
    var content = $('.content-wrapper');

    // Set initial state
    if ($(window).width() < 768) {
      this.closeSidebar();
    } else {
      this.openSidebar();
    }

    // Handle sidebar menu clicks
    $('.sidebar-menu .nav-link').on('click', function(e) {
      var $this = $(this);
      var $parent = $this.parent();

      // Handle treeview
      if ($parent.hasClass('has-treeview')) {
        e.preventDefault();

        if ($parent.hasClass('menu-open')) {
          $parent.removeClass('menu-open');
          $parent.children('.nav-treeview').slideUp(300);
        } else {
          $parent.addClass('menu-open');
          $parent.children('.nav-treeview').slideDown(300);
        }
      }

      // Close sidebar on mobile after click
      if ($(window).width() < 768) {
        AdminLTE.closeSidebar();
      }
    });
  };

  // Initialize Navbar
  AdminLTE.initNavbar = function() {
    // Handle navbar search
    $('.navbar-search-toggle').on('click', function() {
      $('.navbar-search').toggleClass('show');
      $('.navbar-search input').focus();
    });

    // Handle dropdown menus
    $('.navbar .dropdown').on('shown.bs.dropdown', function() {
      $(this).find('.dropdown-menu').addClass('show');
    });

    $('.navbar .dropdown').on('hidden.bs.dropdown', function() {
      $(this).find('.dropdown-menu').removeClass('show');
    });
  };

  // Initialize Tooltips
  AdminLTE.initTooltips = function() {
    if (AdminLTE.options.enableBSTooltip) {
      $('body').tooltip({
        selector: AdminLTE.options.BSTooltipSelector
      });
    }
  };

  // Initialize Layout
  AdminLTE.initLayout = function() {
    // Set content wrapper min height
    this.setContentHeight();
  };

  // Toggle Sidebar
  AdminLTE.toggleSidebar = function() {
    var body = $('body');
    var sidebar = $('.main-sidebar');
    var content = $('.content-wrapper');

    if ($(window).width() >= 768) {
      // Desktop: collapse/expand
      if (body.hasClass('sidebar-collapse')) {
        this.expandSidebar();
      } else {
        this.collapseSidebar();
      }
    } else {
      // Mobile: show/hide overlay
      if (sidebar.hasClass('show')) {
        this.closeSidebar();
      } else {
        this.openSidebar();
      }
    }
  };

  // Collapse Sidebar
  AdminLTE.collapseSidebar = function() {
    $('body').addClass('sidebar-collapse');
    this.saveSidebarState(true);
  };

  // Expand Sidebar
  AdminLTE.expandSidebar = function() {
    $('body').removeClass('sidebar-collapse');
    this.saveSidebarState(false);
  };

  // Open Sidebar (Mobile)
  AdminLTE.openSidebar = function() {
    $('.main-sidebar').addClass('show');
    $('.sidebar-overlay').show();
    $('body').addClass('sidebar-open');
  };

  // Close Sidebar (Mobile)
  AdminLTE.closeSidebar = function() {
    $('.main-sidebar').removeClass('show');
    $('.sidebar-overlay').hide();
    $('body').removeClass('sidebar-open');
  };

  // Handle Window Resize
  AdminLTE.handleWindowResize = function() {
    if ($(window).width() >= 768) {
      this.closeSidebar();
    }
    this.setContentHeight();
  };

  // Set Content Height
  AdminLTE.setContentHeight = function() {
    var windowHeight = $(window).height();
    var navbarHeight = $('.main-header').outerHeight() || 0;
    var footerHeight = $('.main-footer').outerHeight() || 0;

    var contentHeight = windowHeight - navbarHeight - footerHeight;

    $('.content-wrapper').css('min-height', contentHeight + 'px');
  };

  // Save Sidebar State
  AdminLTE.saveSidebarState = function(collapsed) {
    if (typeof collapsed === 'undefined') {
      collapsed = $('body').hasClass('sidebar-collapse');
    }
    localStorage.setItem('sidebar-collapsed', collapsed);
  };

  // Load Sidebar State
  AdminLTE.loadSidebarState = function() {
    var collapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    if (collapsed) {
      $('body').addClass('sidebar-collapse');
    }
  };

  // Initialize on document ready
  $(document).ready(function() {
    AdminLTE.loadSidebarState();
    AdminLTE.init();

    // Show success message if redirected with success parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success')) {
      Swal.fire({
        icon: 'success',
        title: 'Sucesso!',
        text: urlParams.get('success'),
        timer: 3000,
        showConfirmButton: false
      });
    }

    // Show error message if redirected with error parameter
    if (urlParams.get('error')) {
      Swal.fire({
        icon: 'error',
        title: 'Erro!',
        text: urlParams.get('error'),
        timer: 3000,
        showConfirmButton: false
      });
    }
  });

  // Make AdminLTE globally available
  window.AdminLTE = AdminLTE;

})(jQuery);