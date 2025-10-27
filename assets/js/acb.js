document.addEventListener('DOMContentLoaded', function () {
    // Mapeamento das teclas de atalho
    const shortcutKeys = {
        '1': 'btn-high-contrast',
        '2': 'btn-dark-mode',
        '3': 'btn-marker',
        '4': 'btn-line-guide',
        '5': 'btn-increase-font',
        '6': 'btn-decrease-font',
        '7': 'btn-reset'
    };

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

    // Funções de acessibilidade
    document.getElementById('btn-high-contrast').addEventListener('click', function () {
        document.body.classList.toggle('high-contrast');
        this.classList.toggle('active');  // Adiciona ou remove a classe 'active'
    });

    document.getElementById('btn-increase-font').addEventListener('click', function () {
        adjustFontSize(1);
    });

    document.getElementById('btn-decrease-font').addEventListener('click', function () {
        adjustFontSize(-1);
    });

    document.getElementById('btn-gray-scale').addEventListener('click', function () {
        document.body.classList.toggle('grayscale');        
    });

    document.getElementById('btn-negative-contrast').addEventListener('click', function () {
        document.body.classList.toggle('negative-contrast');
        this.classList.toggle('active');  // Adiciona ou remove a classe 'active'
    });

    document.getElementById('btn-underlined-links').addEventListener('click', function () {
        toggleLinkUnderline();
        this.classList.toggle('active');  // Adiciona ou remove a classe 'active'
    });

    document.getElementById('btn-readable-font').addEventListener('click', function () {
        document.body.classList.toggle('readable-font');
        this.classList.toggle('active');  // Adiciona ou remove a classe 'active'
    });

    document.getElementById('btn-reset').addEventListener('click', function () {
        document.body.classList.remove('high-contrast', 'darkmode', 'grayscale', 'negative-contrast', 'readable-font');
        resetFontSize();
        removeLinkUnderline();        
    });

    document.getElementById('btn-dark-mode').addEventListener('click', function () {
        document.body.classList.toggle('darkmode'); // Corrigido
        this.classList.toggle('active');  // Adiciona ou remove a classe 'active'
    });

    document.getElementById('btn-marker').addEventListener('click', function () {
        toggleMarker();        
    });

    document.getElementById('btn-line-guide').addEventListener('click', function () {
        toggleLineGuide();        
    });

    document.getElementById('btn-text-reader').addEventListener('click', function () {
        const text = document.body.innerText; // Captura todo o texto visível da página
        const speech = new SpeechSynthesisUtterance(text);
        speech.lang = 'pt-BR'; // Configura para português
        window.speechSynthesis.speak(speech);
        this.classList.toggle('active');  // Adiciona ou remove a classe 'active'
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

    function toggleLinkUnderline() {
        const links = document.querySelectorAll('a');
        links.forEach(link => {
            link.style.textDecoration = link.style.textDecoration === 'underline' ? 'none' : 'underline';
        });
    }

    function removeLinkUnderline() {
        const links = document.querySelectorAll('a');
        links.forEach(link => {
            link.style.textDecoration = 'none';
        });
    }
});

function toggleToolbar() {
    const toolbar = document.getElementById('accessibility-toolbar');
    toolbar.classList.toggle('accessibility-toolbar-closed');
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
document.addEventListener('DOMContentLoaded', function () {
    // Função para Daltonismo
    document.getElementById('btn-colorblind').addEventListener('click', function () {
        document.body.classList.toggle('high-saturation');
        document.body.classList.toggle('colorblind-contrast');
    });

    // Função para Epilepsia
    document.getElementById('btn-epilepsy').addEventListener('click', function () {
        document.body.classList.toggle('low-saturation');
        document.body.classList.toggle('no-animations');
        document.querySelectorAll('audio, video').forEach(media => media.muted = true);
    });

    // Função para TDAH
    document.getElementById('btn-adhd').addEventListener('click', function () {
        document.body.classList.toggle('readable-font');
        toggleLineGuide();
    });

    // Função para Dislexia
    document.getElementById('btn-dyslexia').addEventListener('click', function () {
        document.body.classList.toggle('dyslexia-font');
        document.body.classList.toggle('underline-links');
    });
});
document.addEventListener('DOMContentLoaded', function () {
    const toolbar = document.getElementById('accessibility-toolbar');
    const toggleButton = document.querySelector('.toolbar-toggle');

    // Expande e colapsa a barra de acessibilidade ao clicar no ícone
    toggleButton.addEventListener('click', function () {
        toolbar.classList.toggle('expanded');
    });
    // Funções adicionais
    document.getElementById('btn-colorblind').addEventListener('click', function () {
        document.body.classList.toggle('high-saturation');
    });

    document.getElementById('btn-epilepsy').addEventListener('click', function () {
        document.body.classList.toggle('low-saturation');
        document.body.classList.toggle('no-animations');
        document.querySelectorAll('audio, video').forEach(media => media.muted = true);
    });

    document.getElementById('btn-adhd').addEventListener('click', function () {
        document.body.classList.toggle('readable-font');
    });

    document.getElementById('btn-dyslexia').addEventListener('click', function () {
        document.body.classList.toggle('dyslexia-font');
        document.body.classList.toggle('underline-links');
    });
});
