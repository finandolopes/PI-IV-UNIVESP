document.addEventListener('DOMContentLoaded', function () {
    const accessibilityBar = document.getElementById('accessibility-bar');
    const toggleButton = document.getElementById('accessibility-toggle');
    const icon = document.getElementById('toggle-icon');
    const shortcutKeys = {
        '1': 'btn-high-contrast',
        '2': 'btn-dark-mode',
        '3': 'btn-marker',
        '4': 'btn-line-guide',
        '5': 'btn-increase-font',
        '6': 'btn-decrease-font',
        '7': 'btn-reset',
        '8': 'btn-vlibras'
    };

    // Função para alternar a visibilidade da barra de acessibilidade
    toggleButton.addEventListener('click', function () {
        accessibilityBar.classList.toggle('active');
        const isActive = accessibilityBar.classList.contains('active');

        icon.classList.toggle('bi-chevron-left', isActive);
        icon.classList.toggle('bi bi-universal-access-circle', !isActive);
        this.setAttribute('aria-expanded', isActive);
    });

    // Função para adicionar a classe alt-pressed ao body ao pressionar Alt
    document.addEventListener('keydown', function (event) {
        if (event.altKey) {
            document.body.classList.add('alt-pressed');
        }
    });

    // Função para remover a classe alt-pressed do body ao soltar Alt
    document.addEventListener('keyup', function (event) {
        if (!event.altKey) {
            document.body.classList.remove('alt-pressed');
        }
    });

    // Função para executar o atalho correspondente ao pressionar Alt + número
    document.addEventListener('keydown', function (event) {
        if (event.altKey && shortcutKeys[event.key]) {
            event.preventDefault(); // Previne ações padrão
            document.getElementById(shortcutKeys[event.key]).click();
        }
    });

    // Funções de controle de acessibilidade
    document.getElementById('btn-high-contrast').addEventListener('click', function () {
        toggleButtonState('btn-high-contrast', 'high-contrast');
    });

    document.getElementById('btn-dark-mode').addEventListener('click', function () {
        toggleButtonState('btn-dark-mode', 'dark-mode');
    });

    document.getElementById('btn-marker').addEventListener('click', function () {
        toggleMarker();
    });

    document.getElementById('btn-line-guide').addEventListener('click', function () {
        toggleLineGuide();
    });

    document.getElementById('btn-increase-font').addEventListener('click', function () {
        adjustFontSize(1);
        highlightFontButtons();
    });

    document.getElementById('btn-decrease-font').addEventListener('click', function () {
        adjustFontSize(-1);
        highlightFontButtons();
    });

    document.getElementById('btn-reset').addEventListener('click', function () {
        document.body.classList.remove('high-contrast', 'dark-mode', 'marker-active', 'line-guide-active');
        resetFontSize();
        resetButtonStates();
        clearMarker();
        clearLineGuide();
    });

    // Função para iniciar o VLibras dentro da barra lateral
    document.getElementById('btn-vlibras').addEventListener('click', function () {
        const vlibrasContainer = document.getElementById('vlibras-container');

        if (!vlibrasContainer.hasChildNodes()) {
            const script = document.createElement('script');
            script.src = "https://vlibras.gov.br/app/vlibras-plugin.js";
            script.onload = function() {
                new window.VLibras.Widget('https://vlibras.gov.br/app', vlibrasContainer);
            };
            vlibrasContainer.appendChild(script);
            vlibrasContainer.style.display = 'block';
        } else {
            vlibrasContainer.classList.toggle('hidden');
        }
    });

    function adjustFontSize(step) {
        const elements = document.querySelectorAll('body, body *:not(script):not(style)');
        elements.forEach(function (el) {
            const currentSize = window.getComputedStyle(el).getPropertyValue('font-size');
            const newSize = parseFloat(currentSize) + step;
            el.style.fontSize = newSize + 'px';
        });
    }

    function resetFontSize() {
        const elements = document.querySelectorAll('body, body *:not(script):not(style)');
        elements.forEach(function (el) {
            el.style.fontSize = '';
        });
    }

    function highlightFontButtons() {
        const fontSize = parseFloat(window.getComputedStyle(document.body).getPropertyValue('font-size'));
        const defaultSize = 16;

        if (fontSize > defaultSize) {
            document.getElementById('btn-increase-font').classList.add('active');
            document.getElementById('btn-decrease-font').classList.remove('active');
        } else if (fontSize < defaultSize) {
            document.getElementById('btn-decrease-font').classList.add('active');
            document.getElementById('btn-increase-font').classList.remove('active');
        } else {
            document.getElementById('btn-increase-font').classList.remove('active');
            document.getElementById('btn-decrease-font').classList.remove('active');
        }
    }

    function resetButtonStates() {
        document.querySelectorAll('.accessibility-btn').forEach(function (button) {
            button.classList.remove('active');
        });
    }

    function toggleMarker() {
        const button = document.getElementById('btn-marker');
        const isActive = button.classList.toggle('active');
        
        if (isActive) {
            document.body.addEventListener('mouseover', applyMarker);
        } else {
            document.body.removeEventListener('mouseover', applyMarker);
            clearMarker();
        }
    }

    function applyMarker(event) {
        clearMarker();
        const element = event.target;

        if (element.nodeType === Node.ELEMENT_NODE && element.tagName.match(/^(P|SPAN|LI|H[1-6])$/i)) {
            element.classList.add('marker-active');
        }
    }

    function clearMarker() {
        document.querySelectorAll('.marker-active').forEach(function (el) {
            el.classList.remove('marker-active');
        });
    }

    function toggleLineGuide() {
        const button = document.getElementById('btn-line-guide');
        const isActive = button.classList.toggle('active');

        if (isActive) {
            document.body.addEventListener('mousemove', applyLineGuide);
            createLineGuide();
        } else {
            document.body.removeEventListener('mousemove', applyLineGuide);
            clearLineGuide();
        }
    }

    function createLineGuide() {
        const line = document.createElement('div');
        line.id = 'line-guide';
        line.style.position = 'fixed';
        line.style.left = '0';
        line.style.right = '0';
        line.style.height = '5px';
        line.style.backgroundColor = 'red';
        line.style.zIndex = '10000';
        document.body.appendChild(line);
    }

    function applyLineGuide(event) {
        const line = document.getElementById('line-guide');
        line.style.top = `${event.clientY}px`;
    }

    function clearLineGuide() {
        const line = document.getElementById('line-guide');
        if (line) line.remove();
    }

    function toggleButtonState(buttonId, className) {
        const button = document.getElementById(buttonId);
        button.classList.toggle('active');
        document.body.classList.toggle(className);
    }
});
