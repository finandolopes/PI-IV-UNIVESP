document.addEventListener('DOMContentLoaded', function () {
    const state = {
        fontSize: 100,
        letterSpacing: 0,
        lineSpacing: 0,
        isDarkModeActive: false,
        isHighContrastActive: false,
        isGrayScaleActive: false,
        isNegativeContrastActive: false,
        readingRulerMode: 'móvel', // 'móvel' ou 'fixo'
        readingMaskMode: 'móvel', // 'móvel' ou 'fixo'
        isMagnifying: false,
        textReaderSpeed: 'normal', // 'normal', 'fast', 'slow'
        saturationLevel: 1,
        epilepsyMode: false,
        tdaMode: false,
        dyslexiaMode: false,
        daltonismMode: 0, // 0: None, 1: Protanopia, 2: Deuteranopia, 3: Tritanopia
        isMotorSkillsModeActive: false,
        isTextReaderActive: false,
        isLibrasActive: false, // Novo estado para Libras
    };

    function loadSettings() {
        const savedState = JSON.parse(localStorage.getItem('accessibilityState'));
        if (savedState) Object.assign(state, savedState);
        applySettings();
    }

    function saveSettings() {
        localStorage.setItem('accessibilityState', JSON.stringify(state));
    }

    function applySettings() {
        adjustFontSize(state.fontSize);
        adjustLetterSpacing(state.letterSpacing);
        adjustLineSpacing(state.lineSpacing);
        document.body.style.filter = `saturate(${state.saturationLevel})`;
        if (state.isDarkModeActive) toggleDarkMode(true);
        if (state.isHighContrastActive) toggleHighContrast(true);
        if (state.isGrayScaleActive) toggleGrayScale(true);
        if (state.isNegativeContrastActive) toggleNegativeContrast(true);
        if (state.isReadingRulerActive) toggleReadingRuler(true);
        if (state.isReadingMaskActive) toggleReadingMask(true);
        if (state.isMotorSkillsModeActive) toggleMotorSkillsMode(true);
        if (state.isTextReaderActive) toggleTextReader(true);
        if (state.isLibrasActive) toggleLibras(true); // Ativar Libras se estiver ativo
    }

    function toggleMenu() {
        const menu = document.getElementById('accessibilityMenu');
        menu.classList.toggle('hidden');
    }

    function createAccessibilityButton() {
        const button = document.createElement('button');
        button.innerHTML = '<i class="fas fa-universal-access fa-3x accessibility-icon"></i>';
        button.className = 'btn btn-primary accessibility-toggle fixed-accessibility-button';
        button.setAttribute('aria-label', 'Menu de Acessibilidade');
        button.title = 'Menu de Acessibilidade';
        document.body.appendChild(button);
        button.onclick = toggleMenu;
        // Estilo do botão para visibilidade e fácil toque
        button.style.position = 'fixed';
        button.style.top = '20%'; // Move para cima (ajuste conforme necessário)
        button.style.transform = 'translateY(0)'; // Remove centralização vertical

        button.style.right = '10px';
        button.style.transform = 'translateY(-50%)';
        button.style.zIndex = '10000';
        button.style.borderRadius = '50%';
        button.style.width = '60px';
        button.style.height = '60px';
        button.style.display = 'flex';
        button.style.justifyContent = 'center';
        button.style.alignItems = 'center';
    }

    function createAccessibilityMenu() {
        const menu = document.createElement('div');
        menu.id = 'accessibilityMenu';
        menu.className = 'accessibility-menu hidden';

        menu.innerHTML = `
           <div class="container-fluid h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="text-primary">Acessibilidade</h3>
                    <button class="btn btn-outline-secondary" onclick="showHelp()">
                        <i class="fas fa-question-circle"></i>
                    </button>
                                    </div>
                                    <div class="row">
                                        <!-- Ajustes de Fonte -->
                                        <div class="col-12 mb-3">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <h5 class="card-title">Ajustes de Fonte</h5>
                                                <div class="d-flex justify-content-around mb-3">
                                                    <div class="text-center icon-button" onclick="increaseFont()">
                                                        <i class="fas fa-search-plus fa-2x text-info"></i>
                                                        <p class="icon-label">Aumentar Fonte</p>
                                                    </div>
                                                    <div class="text-center icon-button" onclick="decreaseFont()">
                                                        <i class="fas fa-search-minus fa-2x text-info"></i>
                                                        <p class="icon-label">Diminuir Fonte</p>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="letterSpacing">Espaçamento entre Letras:</label>
                                                    <input type="range" id="letterSpacing" class="form-control-range" min="0" max="20" value="0" onchange="adjustLetterSpacing(this.value)" />
                                                    <span id="letterSpacingLabel">Espaçamento: 0%</span>
                                                </div>
                                            <div class="form-group">
                                        <label for="lineHeight">Altura da Linha:</label>
                                        <input type="range" id="lineHeight" class="form-control-range" min="1" max="2" step="0.1" value="1.5" onchange="adjustLineHeight(this.value)" />
                                        <span id="lineHeightLabel">Altura: 1.5</span>
                                    </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modos Visuais -->
                    <div class="col-12 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Contrastes e Modos</h5>
                                <div class="d-flex justify-content-around mb-3">
                                    <div class="text-center icon-button" onclick="toggleDarkMode()">
                                        <i id="darkModeIcon" class="fas fa-moon fa-2x text-primary"></i>
                                        <p class="icon-label">Modo Escuro</p>
                                    </div>
                                    <div class="text-center icon-button" onclick="toggleHighContrast()">
                                        <i id="highContrastIcon" class="fas fa-adjust fa-2x text-warning"></i>
                                        <p class="icon-label">Alto Contraste</p>
                                    </div>                                    
                                    <div class="text-center icon-button" onclick="toggleNegativeContrast()">
                                        <i id="negativeContrastIcon" class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                                        <p class="icon-label">Contraste Negativo</p>
                                    </div>
                                    <!-- <div class="text-center icon-button" onclick="toggleGrayScale()">
                                        <i id="grayScaleIcon" class="fas fa-tint fa-2x text-danger"></i>
                                        <p class="icon-label">Tons de Cinza</p>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Ferramentas de Leitura -->
                    <div class="col-12 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Ferramentas de Leitura</h5>
                                <div class="d-flex justify-content-between">
                                    <div class="text-center icon-button" onclick="toggleReadingRuler()">
                                        <i id="readingRulerIcon" class="fas fa-grip-lines fa-2x text-info"></i>
                                        <p class="icon-label">Régua</p>
                                    </div>
                                    <div class="text-center icon-button" onclick="toggleReadingMask()">
                                        <i id="readingMaskIcon" class="fas fa-mask  fa-2x text-info"></i>
                                        <p class="icon-label">Máscara</p>
                                    </div>
                                    <div class="text-center icon-button" onclick="toggleMagnifier()">
                                        <i id="magnifierIcon" class="fas fa-search fa-2x text-info"></i>
                                        <p class="icon-label">Lupa</p>
                                    </div>
                                </div>
                                <div class="text-center mt-2 icon-button">
                                    <i id="textReaderIcon" class="fas fa-volume-up fa-2x" onclick="toggleTextReader()"></i>
                                    <p class="icon-label">Leitura de Texto <span id="textReaderSpeedLabel">(Normal)</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Saturação -->
                    <div class="col-12 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Saturação</h5>
                                <button class="btn btn-outline-info w-100" onclick="toggleSaturation()">Ajustar Saturação</button>
                            </div>
                        </div>
                    </div>
                    <!-- Tipos de  Fontes -->
                    <!--  <label for="font-family">Fonte:</label>
                <select id="font-family" onchange="changeFontFamily(this.value)">
                    <option value="">Padrão</option>
                    <option value="Arial">Arial</option>
                    <option value="Verdana">Verdana</option>
                    <option value="Georgia">Georgia</option>
                    <option value="Times New Roman">Times New Roman</option>
                </select>-->
                    <!-- Modos Especiais -->                    
                    <div class="col-12">                        
                        <button class="btn btn-outline-secondary w-100 mb-1 btn-sm" onclick="toggleDaltonismMode()">Daltonismo <span id="daltonismModeLabel">(Nenhum)</span></button>
                        <button class="btn btn-outline-secondary w-100 mb-1 btn-sm" onclick="toggleLibrasMode()">Libras <span id="librasModeLabel">(Inativo)</span></button>
                    </div>
                    <!-- Resetar Configurações -->
                    <div class="col-12">
                        <button class="btn btn-danger w-100 mt-3" onclick="resetSettings()">Resetar</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(menu);
    }

    // Adicione os estilos CSS diretamente no JavaScript para manter tudo acoplado
    const style = document.createElement('style');
    style.innerHTML = `
        .accessibility-menu {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            max-width: 600px;
            margin: auto;
        }
        .accessibility-menu h3 {
            font-size: 1.5rem;
            color: #007bff;
        }
        .accessibility-menu .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .accessibility-menu .card-body {
            padding: 15px;
        }
        .accessibility-menu .btn {
            border-radius: 20px;
            transition: background-color 0.3s, color 0.3s;
        }
        .accessibility-menu .btn:hover {
            background-color: #007bff;
            color: #fff;
        }
        .accessibility-menu .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #fff;
        }
        .accessibility-menu .btn-outline-info:hover {
            background-color: #17a2b8;
            color: #fff;
        }
        .accessibility-menu .btn-outline-warning:hover {
            background-color: #ffc107;
            color: #fff;
        }
        .accessibility-menu .btn-outline-success:hover {
            background-color: #28a745;
            color: #fff;
        }
        .accessibility-menu .btn-outline-danger:hover {
            background-color: #dc3545;
            color: #fff;
        }
        .accessibility-menu .btn-outline-primary:hover {
            background-color: #007bff;
            color: #fff;
        }
        .accessibility-menu .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #fff;
        }
        .accessibility-menu .icon-button {
            cursor: pointer;
            transition: transform 0.3s, color 0.3s;
        }
        .accessibility-menu .icon-button:hover {
            transform: scale(1.1);
            color: #007bff;
        }
        .accessibility-menu .icon-label {
            font-size: 0.85rem;
        }
        .accessibility-menu .icon-button.selected i {
            color: #007bff;
        }
        .accessibility-menu .icon-button.selected {
            transform: scale(1.1);
        }
        #readingRuler {
            background-color: rgba(255, 255, 0, 0.8);
            position: fixed;
            width: 100%;
            height: 5px;
            z-index: 1000;
        }

        #readingMask {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            pointer-events: none;
        }

        #readingHole {
            width: 100%;
            height: 50px;
            background-color: transparent;
        }

        #magnifier {
            position: fixed;
            border-radius: 50%;
            border: 3px solid #007bff;
            overflow: hidden;
            z-index: 10000;
            pointer-events: none;
        }
        .fixed-accessibility-button {
            font-size: initial !important;
        }
        .accessibility-icon {
            font-size: 3em !important;
            color: white !important;
            pointer-events: none;
        }
    `;
    document.head.appendChild(style);
   

   // Função responsável por aumentar e diminuir as fontes
window.adjustFontSize = function (value) {
    console.log('Adjusting font size to:', value); // Log para depuração
    state.fontSize = value;
    document.querySelectorAll('body *:not(.accessibility-menu, .accessibility-menu *)').forEach(function (el) {
        el.style.fontSize = `${state.fontSize}%`;
    });
};

window.increaseFont = function () {
    if (state.fontSize < 200) {
        state.fontSize += 10;
        adjustFontSize(state.fontSize);
        document.getElementById('fontSizeSlider').value = state.fontSize;
    }
};

window.decreaseFont = function () {
    if (state.fontSize > 80) {
        state.fontSize -= 10;
        adjustFontSize(state.fontSize);
        document.getElementById('fontSizeSlider').value = state.fontSize;
    }
};

// Ajuste do espaço entre as letras
window.adjustLetterSpacing = function (value) {
    console.log('Adjusting letter spacing to:', value); // Log para depuração
    state.letterSpacing = value;
    document.querySelectorAll('body *:not(.accessibility-menu, .accessibility-menu *)').forEach(function (el) {
        el.style.letterSpacing = `${state.letterSpacing}px`;
    });
    document.getElementById('letterSpacingLabel').textContent = `Espaçamento: ${state.letterSpacing}px`;
};

window.increaseLetterSpacing = function () {
    if (state.letterSpacing < 10) {
        state.letterSpacing += 1;
        adjustLetterSpacing(state.letterSpacing);
        document.getElementById('letterSpacingSlider').value = state.letterSpacing;
    }
};

window.decreaseLetterSpacing = function () {
    if (state.letterSpacing > 0) {
        state.letterSpacing -= 1;
        adjustLetterSpacing(state.letterSpacing);
        document.getElementById('letterSpacingSlider').value = state.letterSpacing;
    }
};

// Ajuste o espaço entre as linhas
window.adjustLineHeight = function (value) {
    console.log('Adjusting line height to:', value); // Log para depuração
    state.lineSpacing = value;
    document.querySelectorAll('body *:not(.accessibility-menu, .accessibility-menu *)').forEach(function (el) {
        el.style.lineHeight = value;
    });
    document.getElementById('lineHeightLabel').textContent = `Altura: ${state.lineSpacing}`;
};

window.increaseLineSpacing = function () {
    if (state.lineSpacing < 2) {
        state.lineSpacing += 0.1;
        adjustLineHeight(state.lineSpacing);
        document.getElementById('lineSpacingSlider').value = state.lineSpacing;
    }
};

window.decreaseLineSpacing = function () {
    if (state.lineSpacing > 1) {
        state.lineSpacing -= 0.1;
        adjustLineHeight(state.lineSpacing);
        document.getElementById('lineSpacingSlider').value = state.lineSpacing;
    }
};

    // Funções para alternar modos e aplicar estilos selecionados
    // Modo Escuro
    window.toggleDarkMode = function() {
        state.isDarkModeActive = !state.isDarkModeActive;
        document.body.classList.toggle('dark-mode', state.isDarkModeActive);
        saveSettings();
    };

    // Alto Contraste
    window.toggleHighContrast = function() {
        state.isHighContrastActive = !state.isHighContrastActive;
        document.body.classList.toggle('high-contrast', state.isHighContrastActive);
        saveSettings();
    };

    // Tons de Cinza
    window.toggleGrayScale = function() {
        state.isGrayScaleActive = !state.isGrayScaleActive;
        document.body.classList.toggle('grayscale', state.isGrayScaleActive);
        saveSettings();
    };

   // Função para Contraste Negativo
   window.toggleNegativeContrast = function() {
    state.isNegativeContrastActive = !state.isNegativeContrastActive;
    document.body.classList.toggle('negative-contrast', state.isNegativeContrastActive);
    saveSettings();
};

     // Ferramentas de leitura   

    // Função régua de leitura
    window.toggleReadingRuler = function () {
        state.isReadingRulerActive = !state.isReadingRulerActive;

        if (state.isReadingRulerActive) {
            const ruler = document.createElement('div');
            ruler.id = 'readingRuler';
            ruler.style.position = 'fixed';
            ruler.style.width = '100%';
            ruler.style.height = '40px';
            ruler.style.backgroundColor = 'rgba(255, 255, 0, 0.5)';
            ruler.style.border = '2px solid rgba(0, 0, 0, 0.3)';
            ruler.style.zIndex = '1000';
            ruler.style.pointerEvents = 'none';
            document.body.appendChild(ruler);

            if (state.readingRulerMode === 'móvel') {
                document.addEventListener('mousemove', moveRuler);
            } else {
                setRulerFixed(ruler);
            }
        } else {
            const ruler = document.getElementById('readingRuler');
            if (ruler) ruler.remove();
            document.removeEventListener('mousemove', moveRuler);
        }
    };

    function moveRuler(event) {
        const ruler = document.getElementById('readingRuler');
        if (ruler) {
            ruler.style.top = `${event.clientY - 20}px`;
        }
    }

    function setRulerFixed(ruler) {
        ruler.style.top = '50%';
        ruler.style.transform = 'translateY(-50%)';
    }

  // Função para Máscara de Leitura
window.toggleReadingMask = function () {
    state.isReadingMaskActive = !state.isReadingMaskActive;
    console.log('Reading Mask Active:', state.isReadingMaskActive); // Log para depuração

    if (state.isReadingMaskActive) {
        const mask = document.createElement('div');
        mask.id = 'readingMask';
        mask.style.position = 'fixed';
        mask.style.width = '100%';
        mask.style.height = '100%';
        mask.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
        mask.style.pointerEvents = 'none';
        mask.style.zIndex = '1000';

        const hole = document.createElement('div');
        hole.style.position = 'absolute';
        hole.style.width = '100%';
        hole.style.height = '120px';
        hole.style.backgroundColor = 'transparent'; // Faixa transparente
        hole.style.boxShadow = '0 0 0 9999px rgba(0, 0, 0, 0.8)'; // Efeito de máscara
        hole.style.top = '50%';
        hole.style.transform = 'translateY(-50%)';
        mask.appendChild(hole);

        document.body.appendChild(mask);
        console.log('Reading Mask Created'); // Log para depuração

        if (state.readingMaskMode === 'móvel') {
            document.addEventListener('mousemove', moveMask);
        } else {
            setMaskFixed(hole);
        }
    } else {
        const mask = document.getElementById('readingMask');
        if (mask) mask.remove();
        document.removeEventListener('mousemove', moveMask);
        console.log('Reading Mask Removed'); // Log para depuração
    }
};

function moveMask(event) {
    const hole = document.querySelector('#readingMask div');
    if (hole) {
        hole.style.top = `${event.clientY - 60}px`;
    }
}

function setMaskFixed(hole) {
    hole.style.top = '50%';
    hole.style.transform = 'translateY(-50%)';
}

// Alterna entre os modos "móvel" e "fixo" para régua e máscara
window.toggleRulerMode = function() {
    state.readingRulerMode = state.readingRulerMode === 'móvel' ? 'fixo' : 'móvel';
    if (state.isReadingRulerActive) toggleReadingRuler();
    toggleReadingRuler();
};

window.toggleMaskMode = function() {
    state.readingMaskMode = state.readingMaskMode === 'móvel' ? 'fixo' : 'móvel';
    if (state.isReadingMaskActive) toggleReadingMask();
    toggleReadingMask();
};

    // Função que altera o tipo de Fonte
    window.changeFontFamily = function(font) {
        document.body.style.fontFamily = font;  // Altera a família da fonte do conteúdo
    };     

 // Lupa
window.toggleMagnifier = function() {
    state.isMagnifying = !state.isMagnifying;
    if (state.isMagnifying) {
        document.body.style.cursor = 'zoom-in';
        document.addEventListener('click', startMagnifying);
        document.addEventListener('contextmenu', stopMagnifying);
    } else {
        document.body.style.cursor = 'default';
        document.removeEventListener('click', startMagnifying);
        document.removeEventListener('contextmenu', stopMagnifying);
        resetZoom();
    }
};

function startMagnifying(event) {
    const zoomedElement = document.elementFromPoint(event.clientX, event.clientY);
    if (zoomedElement) {
        let currentScale = parseFloat(zoomedElement.getAttribute('data-scale')) || 1;
        if (currentScale < 3) {
            currentScale += 0.5;
        } else {
            currentScale = 1;
        }
        zoomedElement.style.transform = `scale(${currentScale})`;
        zoomedElement.style.transition = 'transform 0.2s ease';
        zoomedElement.setAttribute('data-scale', currentScale);
        zoomedElement.classList.add('zoomed');
    }
}

function stopMagnifying(event) {
    event.preventDefault(); // Previne o menu de contexto padrão
    const zoomedElement = document.elementFromPoint(event.clientX, event.clientY);
    if (zoomedElement && zoomedElement.classList.contains('zoomed')) {
        zoomedElement.style.transform = 'scale(1)';
        zoomedElement.style.transition = 'transform 0.2s ease';
        zoomedElement.removeAttribute('data-scale');
        zoomedElement.classList.remove('zoomed');
    }
}

function resetZoom() {
    const zoomedElements = document.querySelectorAll('.zoomed');
    zoomedElements.forEach(el => {
        el.style.transform = 'scale(1)';
        el.style.transition = 'transform 0.2s ease';
        el.removeAttribute('data-scale');
        el.classList.remove('zoomed');
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const state = {
        isTextReaderActive: false,
        textReaderSpeed: 'normal',
    };

// Função Leitura de Texto acessível globalmente
    window.toggleTextReader = function() {
        state.isTextReaderActive = !state.isTextReaderActive;
        console.log('Text Reader Active:', state.isTextReaderActive); // Log para depuração

        if (state.isTextReaderActive) {
            document.addEventListener('mouseover', startTextReader);
            document.addEventListener('mouseout', stopTextReader);
            document.addEventListener('mouseup', handleTextSelection);
        } else {
            document.removeEventListener('mouseover', startTextReader);
            document.removeEventListener('mouseout', stopTextReader);
            document.removeEventListener('mouseup', handleTextSelection);
            speechSynthesis.cancel();
        }
        saveSettings();
    };

    function startTextReader(event) {
        const target = event.target;
        if (target && target.textContent.trim()) {
            target.textReaderTimeout = setTimeout(() => {
                const utterance = new SpeechSynthesisUtterance(target.textContent);
                utterance.rate = state.textReaderSpeed === 'normal' ? 1 : state.textReaderSpeed === 'fast' ? 1.5 : 0.75;
                console.log('Speaking:', target.textContent); // Log para depuração
                speechSynthesis.speak(utterance);
            }, 3000); // 3 segundos
        }
    }

    function stopTextReader(event) {
        const target = event.target;
        if (target && target.textReaderTimeout) {
            clearTimeout(target.textReaderTimeout);
            speechSynthesis.cancel();
            console.log('Speech synthesis canceled'); // Log para depuração
        }
    }

    function handleTextSelection() {
        const selection = window.getSelection().toString();
        if (selection) {
            const utterance = new SpeechSynthesisUtterance(selection);
            utterance.rate = state.textReaderSpeed === 'normal' ? 1 : state.textReaderSpeed === 'fast' ? 1.5 : 0.75;
            console.log('Speaking selected text:', selection); // Log para depuração
            speechSynthesis.speak(utterance);
        }
    }

    function setReaderSpeed(speed) {
        state.textReaderSpeed = speed;
        const speedLabel = document.getElementById('textReaderSpeedLabel');
        if (speedLabel) {
            speedLabel.textContent = `(${speed.charAt(0).toUpperCase() + speed.slice(1)})`;
        }
        console.log('Reader speed set to:', speed); // Log para depuração
        saveSettings();
    }

    const textReaderIcon = document.getElementById('textReaderIcon');
    if (textReaderIcon) {
        textReaderIcon.addEventListener('click', function() {
            if (state.textReaderSpeed === 'normal') {
                setReaderSpeed('fast');
            } else if (state.textReaderSpeed === 'fast') {
                setReaderSpeed('slow');
            } else {
                setReaderSpeed('normal');
            }
            console.log('Text reader speed toggled'); // Log para depuração
        });
    }

    function saveSettings() {
        localStorage.setItem('accessibilityState', JSON.stringify(state));
    }
});

   // Função de Saturação
   window.toggleSaturation = function() {
    if (state.saturationLevel < 10) {
        state.saturationLevel += 1;
    } else {
        state.saturationLevel = 1;
    }
    document.body.style.filter = `saturate(${state.saturationLevel})`;
    saveSettings();
};

// Funções para modos específicos (Dislexia, TDAH, Epilepsia, Daltonismo, Habilidades Motoras)
// Função para alternar o modo Dislexia
window.toggleDyslexiaMode = function() {
    state.dyslexiaMode = !state.dyslexiaMode;
    document.body.classList.toggle('dyslexia-mode', state.dyslexiaMode);

    if (state.dyslexiaMode) {
        applyDyslexiaSettings();
    } else {
        removeDyslexiaSettings();
    }
    saveSettings();
};

function applyDyslexiaSettings() {
    // Alterar a fonte para "OpenDyslexic"
    document.body.style.fontFamily = 'OpenDyslexic, Arial, sans-serif';

    // Ajustar espaçamento entre letras, palavras e linhas
    document.body.style.letterSpacing = '0.12em';
    document.body.style.wordSpacing = '0.16em';
    document.body.style.lineHeight = '1.5';

    // Sublinhado para links
    document.querySelectorAll('a').forEach(function(link) {
        link.style.textDecoration = 'underline';
    });

    // Adicionar régua de leitura
    toggleReadingRuler(true);
}

function removeDyslexiaSettings() {
    // Reverter as alterações de fonte e espaçamento
    document.body.style.fontFamily = '';
    document.body.style.letterSpacing = '';
    document.body.style.wordSpacing = '';
    document.body.style.lineHeight = '';

    // Remover sublinhado de links
    document.querySelectorAll('a').forEach(function(link) {
        link.style.textDecoration = '';
    });

    // Remover régua de leitura
    toggleReadingRuler(false);
}

// Função para alternar a régua de leitura
window.toggleReadingRuler = function(forceState) {
    if (typeof forceState === 'boolean') {
        state.isReadingRulerActive = forceState;
    } else {
        state.isReadingRulerActive = !state.isReadingRulerActive;
    }

    if (state.isReadingRulerActive) {
        const ruler = document.createElement('div');
        ruler.id = 'readingRuler';
        ruler.style.position = 'fixed';
        ruler.style.width = '100%';
        ruler.style.height = '40px';
        ruler.style.backgroundColor = 'rgba(255, 255, 0, 0.5)';
        ruler.style.border = '2px solid rgba(0, 0, 0, 0.3)';
        ruler.style.zIndex = '1000';
        ruler.style.pointerEvents = 'none';
        document.body.appendChild(ruler);

        document.addEventListener('mousemove', moveRuler);
    } else {
        const ruler = document.getElementById('readingRuler');
        if (ruler) ruler.remove();
        document.removeEventListener('mousemove', moveRuler);
    }
};

function moveRuler(event) {
    const ruler = document.getElementById('readingRuler');
    if (ruler) {
        ruler.style.top = `${event.clientY - 20}px`;
    }
}

function saveSettings() {
    localStorage.setItem('accessibilityState', JSON.stringify(state));
}

// Função para alternar o modo TDAH
window.toggleTDAMode = function () {
    state.tdaMode = !state.tdaMode;
    document.body.classList.toggle('tda-mode', state.tdaMode);

    if (state.tdaMode) {
        toggleReadingRuler(); // Ativa a régua de leitura
        toggleTextReader(true); // Ativa a leitura de texto
        startPomodoro(); // Inicia o modo Pomodoro
    } else {
        toggleReadingRuler(); // Desativa a régua de leitura
        toggleTextReader(false); // Desativa a leitura de texto
        stopPomodoro(); // Para o modo Pomodoro
    }
};

// Função para iniciar o modo Pomodoro
function startPomodoro() {
    const pomodoroDuration = 25 * 60 * 1000; // 25 minutos
    const breakDuration = 5 * 60 * 1000; // 5 minutos

    function startBreak() {
        alert('Hora do intervalo! 5 minutos de descanso.');
        setTimeout(startPomodoro, breakDuration);
    }

    alert('Modo Pomodoro iniciado! 25 minutos de foco.');
    setTimeout(startBreak, pomodoroDuration);
}

// Função para parar o modo Pomodoro
function stopPomodoro() {
    alert('Modo Pomodoro desativado.');
    // Adicione lógica para parar o modo Pomodoro, se necessário
}

// Função para leitura de texto com destaque
function startTextReader(event) {
    const target = event.target;
    if (target && target.innerText.trim() && !target.closest('.accessibility-menu')) {
        target.textReaderTimeout = setTimeout(() => {
            const utterance = new SpeechSynthesisUtterance(target.innerText);
            utterance.rate = state.textReaderSpeed === 'normal' ? 1 : state.textReaderSpeed === 'fast' ? 1.5 : state.textReaderSpeed === 'slow' ? 0.75 : 1;
            utterance.onboundary = function(event) {
                const charIndex = event.charIndex;
                highlightText(target, charIndex);
            };
            console.log('Speaking:', target.innerText); // Log para depuração
            speechSynthesis.speak(utterance);
        }, 3000); // 3 segundos
    }
}

function highlightText(element, charIndex) {
    const text = element.innerText;
    const before = text.slice(0, charIndex);
    const after = text.slice(charIndex);
    element.innerHTML = `<span style="background-color: yellow;">${before}</span>${after}`;
}

function stopTextReader(event) {
    const target = event.target;
    if (target && target.textReaderTimeout) {
        clearTimeout(target.textReaderTimeout);
        speechSynthesis.cancel();
        console.log('Speech synthesis canceled'); // Log para depuração
        removeHighlight(target);
    }
}

function removeHighlight(element) {
    element.innerHTML = element.innerText;
}

// Função para alternar a leitura de texto
window.toggleTextReader = function(forceState) {
    if (typeof forceState === 'boolean') {
        state.isTextReaderActive = forceState;
    } else {
        state.isTextReaderActive = !state.isTextReaderActive;
    }
    console.log('Text Reader Active:', state.isTextReaderActive); // Log para depuração

    if (state.isTextReaderActive) {
        document.addEventListener('mouseover', startTextReader);
        document.addEventListener('mouseout', stopTextReader);
    } else {
        document.removeEventListener('mouseover', startTextReader);
        document.removeEventListener('mouseout', stopTextReader);
        speechSynthesis.cancel();
    }
    saveSettings();
};

// Função modo Epilepsia
window.toggleEpilepsyMode = function() {
    state.epilepsyMode = !state.epilepsyMode;
    document.body.classList.toggle('epilepsy-mode', state.epilepsyMode);

    if (state.epilepsyMode) {
        applyEpilepsySettings();
        alert('Modo Epilepsia Ativado');
    } else {
        removeEpilepsySettings();
        alert('Modo Epilepsia Desativado');
    }
    saveSettings();
};

function applyEpilepsySettings() {
    // Desativar animações e efeitos visuais
    const style = document.createElement('style');
    style.id = 'epilepsyModeStyles';
    style.innerHTML = `
        * {
            animation: none !important;
            transition: none !important;
        }
        img, video {
            filter: brightness(0.8) contrast(0.8);
        }
        body {
            filter: brightness(0.8) contrast(0.8);
        }
    `;
    document.head.appendChild(style);

    // Adicionar aviso sobre conteúdo com movimento
    const warning = document.createElement('div');
    warning.id = 'epilepsyWarning';
    warning.style.position = 'fixed';
    warning.style.top = '0';
    warning.style.left = '0';
    warning.style.width = '100%';
    warning.style.backgroundColor = 'red';
    warning.style.color = 'white';
    warning.style.textAlign = 'center';
    warning.style.padding = '10px';
    warning.style.zIndex = '10000';
    warning.innerText = 'Aviso: Este site contém conteúdo com movimento e luzes piscantes. Use com cautela.';
    document.body.appendChild(warning);
}

function removeEpilepsySettings() {
    // Reverter desativação de animações e efeitos visuais
    const style = document.getElementById('epilepsyModeStyles');
    if (style) style.remove();

    // Remover aviso sobre conteúdo com movimento
    const warning = document.getElementById('epilepsyWarning');
    if (warning) warning.remove();
}

function saveSettings() {
    localStorage.setItem('accessibilityState', JSON.stringify(state));
}

// Função para alternar o modo de habilidades motoras
window.toggleMotorSkillsMode = function() {
    state.isMotorSkillsModeActive = !state.isMotorSkillsModeActive;
    document.body.classList.toggle('motor-skills-mode', state.isMotorSkillsModeActive);

    if (state.isMotorSkillsModeActive) {
        applyMotorSkillsSettings();
    } else {
        removeMotorSkillsSettings();
    }
    saveSettings();
};

function applyMotorSkillsSettings() {
    // Adicionar estilos para navegação por teclado e botões ampliados
    const style = document.createElement('style');
    style.id = 'motorSkillsModeStyles';
    style.innerHTML = `
        *:focus {
            outline: 3px solid #007bff !important; /* Focalização visível */
            outline-offset: 2px;
        }
        button, a, input, textarea, select {
            padding: 15px !important; /* Botões ampliados */
            margin: 5px !important; /* Espaçamento entre botões */
            font-size: 1.2em !important; /* Texto maior */
        }
        .motor-skills-mode .confirm-click {
            position: relative;
        }
        .motor-skills-mode .confirm-click::after {
            content: 'Clique novamente para confirmar';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #ffc107;
            color: #000;
            padding: 5px;
            border-radius: 5px;
            display: none;
        }
        .motor-skills-mode .confirm-click.active::after {
            display: block;
        }
    `;
    document.head.appendChild(style);

    // Adicionar evento para prevenção de acionamento acidental
    document.querySelectorAll('button, a').forEach(function(el) {
        el.classList.add('confirm-click');
        el.addEventListener('click', handleConfirmClick);
    });
}

function removeMotorSkillsSettings() {
    // Reverter estilos para navegação por teclado e botões ampliados
    const style = document.getElementById('motorSkillsModeStyles');
    if (style) style.remove();

    // Remover evento para prevenção de acionamento acidental
    document.querySelectorAll('button, a').forEach(function(el) {
        el.classList.remove('confirm-click');
        el.removeEventListener('click', handleConfirmClick);
    });
}

function handleConfirmClick(event) {
    const el = event.currentTarget;
    if (!el.classList.contains('active')) {
        event.preventDefault();
        el.classList.add('active');
        setTimeout(() => el.classList.remove('active'), 2000); // 2 segundos para confirmar
    }
}

function saveSettings() {
    localStorage.setItem('accessibilityState', JSON.stringify(state));
}

// Função para alternar o modo de daltonismo
window.toggleDaltonismMode = function() {
    state.daltonismMode = (state.daltonismMode + 1) % 4;
    document.body.classList.remove('protanopia', 'deuteranopia', 'tritanopia');
    if (state.daltonismMode === 1) {
        document.body.classList.add('protanopia');
        document.getElementById('daltonismModeLabel').textContent = '(Protanopia)';
    } else if (state.daltonismMode === 2) {
        document.body.classList.add('deuteranopia');
        document.getElementById('daltonismModeLabel').textContent = '(Deuteranopia)';
    } else if (state.daltonismMode === 3) {
        document.body.classList.add('tritanopia');
        document.getElementById('daltonismModeLabel').textContent = '(Tritanopia)';
    } else {
        document.getElementById('daltonismModeLabel').textContent = '(Nenhum)';
    }
    saveSettings();
};

// Adicione os estilos CSS diretamente no JavaScript para manter tudo acoplado
const daltonismStyle = document.createElement('style');
daltonismStyle.innerHTML = `
    .protanopia *:not(.menu):not(.menu *) {
        filter: url(#protanopia);
    }
    .deuteranopia *:not(.menu):not(.menu *) {
        filter: url(#deuteranopia);
    }
    .tritanopia *:not(.menu):not(.menu *) {
        filter: url(#tritanopia);
    }
    .protanopia img:not(.menu img) {
        filter: url(#protanopia) !important;
    }
    .deuteranopia img:not(.menu img) {
        filter: url(#deuteranopia) !important;
    }
    .tritanopia img:not(.menu img) {
        filter: url(#tritanopia) !important;
    }
`;
document.head.appendChild(daltonismStyle);

// Adicione os filtros SVG para daltonismo
const svgFilters = `
<svg xmlns="http://www.w3.org/2000/svg" version="1.1">
    <defs>
        <filter id="protanopia">
            <feColorMatrix type="matrix" values="0.567, 0.433, 0, 0, 0, 0.558, 0.442, 0, 0, 0, 0, 0.242, 0.758, 0, 0, 0, 0, 0, 1, 0"/>
        </filter>
        <filter id="deuteranopia">
            <feColorMatrix type="matrix" values="0.625, 0.375, 0, 0, 0, 0.7, 0.3, 0, 0, 0, 0, 0.3, 0.7, 0, 0, 0, 0, 0, 1, 0"/>
        </filter>
        <filter id="tritanopia">
            <feColorMatrix type="matrix" values="0.95, 0.05, 0, 0, 0, 0, 0.433, 0.567, 0, 0, 0, 0.475, 0.525, 0, 0, 0, 0, 0, 1, 0"/>
        </filter>
    </defs>
</svg>
`;
const svgContainer = document.createElement('div');
svgContainer.style.display = 'none';
svgContainer.innerHTML = svgFilters;
document.body.appendChild(svgContainer);

function saveSettings() {
    localStorage.setItem('accessibilityState', JSON.stringify(state));
}

// Função para habilitar/desabilitar o VLibras
window.toggleLibrasMode = function () {
    state.isLibrasActive = !state.isLibrasActive;

    if (state.isLibrasActive) {
        // Verifica se o VLibras já foi carregado
        if (!window.vlibrasLoaded) {
            const script = document.createElement('script');
            script.src = 'https://vlibras.gov.br/app/vlibras-plugin.js';
            script.defer = true;
            script.onload = function () {
                window.VLibras = new window.VLibras.Widget();
                window.vlibrasLoaded = true;
                document.querySelector('[vw]').classList.add('enabled');
            };
            document.body.appendChild(script);
        } else {
            document.querySelector('[vw]').classList.add('enabled');
        }
    } else {
        document.querySelector('[vw]').classList.remove('enabled');
    }

    saveSettings();
    document.getElementById('librasModeLabel').textContent = state.isLibrasActive ? '(Ativo)' : '(Inativo)';
};



// Resetar Configurações
window.resetSettings = function() {
    state.fontSize = 100;
    state.letterSpacing = 0;
}

// Resetar Configurações
window.resetSettings = function() {
    state.fontSize = 100;
    state.letterSpacing = 0;
    state.lineSpacing = 0;
    state.isDarkModeActive = false;
    state.isHighContrastActive = false;
    state.isGrayScaleActive = false;
    state.isNegativeContrastActive = false;
    state.isReadingRulerActive = false;
    state.isReadingMaskActive = false;
    state.isMagnifying = false;
    state.saturationLevel = 1;
    state.tdaMode = false;
    state.dyslexiaMode = false;
    state.epilepsyMode = false;
    state.isMotorSkillsModeActive = false;
    state.daltonismMode = 0;
    state.isTextReaderActive = false;

    document.body.style.fontSize = '100%';
    document.body.style.letterSpacing = '0px';
    document.body.style.lineHeight = 'normal';
    document.body.style.filter = 'none';
    document.body.classList.remove('dark-mode', 'high-contrast', 'grayscale', 'negative-contrast', 
                                     'protanopia', 'deuteranopia', 'tritanopia', 'tda-mode', 'dyslexia-mode', 
                                     'epilepsy-mode', 'motor-skills-mode');

    const ruler = document.getElementById('readingRuler');
    if (ruler) ruler.remove();

    const mask = document.getElementById('readingMask');
    if (mask) mask.remove();

    document.body.style.cursor = 'default';

    saveSettings();
};

    // Exibir ajuda (integrado ao SweetAlert2)
window.showHelp = function() {
    Swal.fire({
        title: 'Ajuda de Acessibilidade',
        html: `
            <ul style="text-align: left;">
                <li><strong>Aumentar Fonte:</strong> Aumenta o tamanho da fonte do texto.</li>
                <li><strong>Diminuir Fonte:</strong> Diminui o tamanho da fonte do texto.</li>
                <li><strong>Espaçamento entre Letras:</strong> Ajusta o espaçamento entre as letras do texto.</li>
                <li><strong>Altura da Linha:</strong> Ajusta a altura das linhas do texto.</li>
                <li><strong>Modo Escuro:</strong> Alterna o modo escuro para reduzir a luminosidade da tela.</li>
                <li><strong>Alto Contraste:</strong> Aumenta o contraste do site para melhorar a legibilidade.</li>
                <li><strong>Contraste Negativo:</strong> Inverte as cores para um contraste negativo.</li>
                <li><strong>Régua de Leitura:</strong> Mostra uma régua que segue o cursor do mouse para ajudar na leitura.</li>
                <li><strong>Máscara de Leitura:</strong> Adiciona uma máscara opaca com uma faixa para destacar o texto.</li>
                <li><strong>Lupa:</strong> Amplia o conteúdo sob o cursor para melhor visualização.</li>
                <li><strong>Leitura de Texto:</strong> Lê em voz alta o texto selecionado ou sob o cursor.</li>
                <li><strong>Saturação:</strong> Ajusta o nível de saturação das cores do site.</li>
                <li><strong>Dislexia:</strong> Ajusta a fonte e sublinha links para melhorar a legibilidade para pessoas com dislexia.</li>
                <li><strong>TDAH:</strong> Bloqueia animações e ativa a régua de leitura e leitura de texto para ajudar pessoas com TDAH.</li>
                <li><strong>Anti-Epilepsia:</strong> Desativa animações e sons para evitar gatilhos de epilepsia.</li>
                <li><strong>Habilidades Motoras:</strong> Ativa a navegação por teclado com maior visibilidade e botões ampliados.</li>
                <li><strong>Daltonismo:</strong> Alterna entre modos de daltonismo (Protanopia, Deuteranopia, Tritanopia) para ajustar as cores do site.</li>
                <li><strong>Resetar:</strong> Reseta todas as configurações de acessibilidade para os valores padrão.</li>
            </ul>
        `,
        icon: 'info',
        confirmButtonText: 'Ok'
    });
};

// Atalhos de Teclado
document.addEventListener('keydown', function(event) {
    if (event.altKey) {
        switch (event.key) {
            case '1':
                toggleHighContrast();
                break;
            case '2':
                toggleDarkMode();
                break;
            case '3':
                increaseFont();
                break;
            case '4':
                decreaseFont();
                break;
            case '5':
                resetSettings();
                break;
            case 'e':
                toggleNegativeContrast();
                break;
            case 'r':
                toggleColorBlindMode();
                break;
            case 'g':
                toggleGrayScale();
                break;
            case 'u':
                toggleUnderlineLinks();
                break;
        }
    }
});

    // Criação do botão e menu de acessibilidade
    createAccessibilityButton();
    createAccessibilityMenu();
    loadSettings();
});

// Estilo CSS para o menu
const style = document.createElement('style');
style.innerHTML = `
    .accessibility-menu {
        position: fixed;
        top: 10%;
        right: 0;
        width: 320px;
        background-color: #f8f9fa;
        border-radius: 15px 0 0 15px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        z-index: 1040;
        overflow-y: auto;
        max-height: 90vh;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    }
    .hidden {
        transform: translateX(0);
    }
    .accessibility-toggle {
        position: fixed;
        top: 50%;
        right: 0;
        z-index: 1050;
        border-radius: 50%;
        background-color: #007bff;
        color: white;
        border: none;
        padding: 10px;
    }
     body.high-contrast {
    /* This stylesheet based on 0WonB.css generated by Accessibility CSS Generator, (c) Silas S. Brown 2006-2015.  Version 0.9844 */
  }
  body.contrast .placebo {
    line-height: normal;
  }
  body.contrast * {
    -webkit-box-shadow: none !important;
    box-shadow: none !important;
  }
  body.high-contrast a,
  body.high-contrast abbr,
  body.high-contrast acronym,
  body.high-contrast address,
  body.high-contrast article,
  body.high-contrast aside,
  body.high-contrast b,
  body.high-contrast basefont,
  body.high-contrast bdi,
  body.high-contrast big,
  body.high-contrast blink,
  body.high-contrast blockquote,
  body.high-contrast body,
  body.high-contrast button,
  body.high-contrast canvas,
  body.high-contrast caption,
  body.high-contrast center,
  body.high-contrast cite,
  body.high-contrast code,
  body.high-contrast col,
  body.high-contrast colgroup,
  body.high-contrast command,
  body.high-contrast dd,
  body.high-contrast del,
  body.high-contrast details,
  body.high-contrast dfn,
  body.high-contrast dir,
  body.high-contrast div,
  body.high-contrast dl,
  body.high-contrast dt,
  body.high-contrast em,
  body.high-contrast embed,
  body.high-contrast fieldset,
  body.high-contrast figcaption,
  body.high-contrast figure,
  body.high-contrast font,
  body.high-contrast footer,
  body.high-contrast form,
  body.high-contrast h1,
  body.high-contrast h1 a,
  body.high-contrast h1 a b,
  body.high-contrast h1 abbr,
  body.high-contrast h1 b,
  body.high-contrast h1 center,
  body.high-contrast h1 em,
  body.high-contrast h1 i,
  body.high-contrast h1 span,
  body.high-contrast h1 strong,
  body.high-contrast h2,
  body.high-contrast h2 a,
  body.high-contrast h2 a b,
  body.high-contrast h2 abbr,
  body.high-contrast h2 b,
  body.high-contrast h2 center,
  body.high-contrast h2 em,
  body.high-contrast h2 i,
  body.high-contrast h2 span,
  body.high-contrast h2 strong,
  body.high-contrast h3,
  body.high-contrast h3 a,
  body.high-contrast h3 a b,
  body.high-contrast h3 abbr,
  body.high-contrast h3 b,
  body.high-contrast h3 center,
  body.high-contrast h3 em,
  body.high-contrast h3 i,
  body.high-contrast h3 span,
  body.high-contrast h3 strong,
  body.high-contrast h4,
  body.high-contrast h4 a,
  body.high-contrast h4 a b,
  body.high-contrast h4 abbr,
  body.high-contrast h4 b,
  body.high-contrast h4 center,
  body.high-contrast h4 em,
  body.high-contrast h4 i,
  body.high-contrast h4 span,
  body.high-contrast h4 strong,
  body.high-contrast h5,
  body.high-contrast h5 a,
  body.high-contrast h5 a b,
  body.high-contrast h5 abbr,
  body.high-contrast h5 b,
  body.high-contrast h5 center,
  body.high-contrast h5 em,
  body.high-contrast h5 i,
  body.high-contrast h5 span,
  body.high-contrast h5 strong,
  body.high-contrast h6,
  body.high-contrast h6 a,
  body.high-contrast h6 a b,
  body.high-contrast h6 abbr,
  body.high-contrast h6 b,
  body.high-contrast h6 center,
  body.high-contrast h6 em,
  body.high-contrast h6 i,
  body.high-contrast h6 span,
  body.high-contrast h6 strong,
  body.high-contrast header,
  body.high-contrast hgroup,
  body.high-contrast html,
  body.high-contrast i,
  body.high-contrast iframe,
  body.high-contrast img,
  body.high-contrast input,
  body.high-contrast ins,
  body.high-contrast kbd,
  body.high-contrast label,
  body.high-contrast legend,
  body.high-contrast li,
  body.high-contrast listing,
  body.high-contrast main,
  body.high-contrast mark,
  body.high-contrast marquee,
  body.high-contrast menu,
  body.high-contrast meter,
  body.high-contrast multicol,
  body.high-contrast nav,
  body.high-contrast nobr,
  body.high-contrast object,
  body.high-contrast ol,
  body.high-contrast option,
  body.high-contrast output,
  body.high-contrast p,
  body.high-contrast plaintext,
  body.high-contrast pre,
  body.high-contrast progress,
  body.high-contrast q,
  body.high-contrast rb,
  body.high-contrast rp,
  body.high-contrast rt,
  body.high-contrast ruby,
  body.high-contrast s,
  body.high-contrast samp,
  body.high-contrast section,
  body.high-contrast select,
  body.high-contrast small,
  body.high-contrast span,
  body.high-contrast strike,
  body.high-contrast strong,
  body.high-contrast sub,
  body.high-contrast summary,
  body.high-contrast sup,
  body.high-contrast svg,
  body.high-contrast table,
  body.high-contrast tbody,
  body.high-contrast td,
  body.high-contrast text,
  body.high-contrast textarea,
  body.high-contrast th,
  body.high-contrast thead,
  body.high-contrast time,
  body.high-contrast tr,
  body.high-contrast tt,
  body.high-contrast u,
  body.high-contrast ul,
  body.high-contrast var,
  body.high-contrast video,
  body.high-contrast xmp {
    -moz-appearance: none !important;
    -moz-user-select: text !important;
    -webkit-user-select: text !important;
    background-image: none !important;
    text-shadow: none !important;
    user-select: text !important;
  }
  body.high-contrast a,
  body.high-contrast abbr,
  body.high-contrast acronym,
  body.high-contrast address,
  body.high-contrast article,
  body.high-contrast aside,
  body.high-contrast b,
  body.high-contrast basefont,
  body.high-contrast bdi,
  body.high-contrast big,
  body.high-contrast blink,
  body.high-contrast blockquote,
  body.high-contrast body,
  body.high-contrast canvas,
  body.high-contrast caption,
  body.high-contrast center,
  body.high-contrast cite,
  body.high-contrast code,
  body.high-contrast col,
  body.high-contrast colgroup,
  body.high-contrast command,
  body.high-contrast dd,
  body.high-contrast del,
  body.high-contrast details,
  body.high-contrast dfn,
  body.high-contrast dir,
  body.high-contrast div,
  body.high-contrast dl,
  body.high-contrast dt,
  body.high-contrast em,
  body.high-contrast embed,
  body.high-contrast fieldset,
  body.high-contrast figcaption,
  body.high-contrast figure,
  body.high-contrast font,
  body.high-contrast footer,
  body.high-contrast form,
  body.high-contrast h1,
  body.high-contrast h1 a,
  body.high-contrast h1 a b,
  body.high-contrast h1 abbr,
  body.high-contrast h1 b,
  body.high-contrast h1 center,
  body.high-contrast h1 em,
  body.high-contrast h1 i,
  body.high-contrast h1 span,
  body.high-contrast h1 strong,
  body.high-contrast h2,
  body.high-contrast h2 a,
  body.high-contrast h2 a b,
  body.high-contrast h2 abbr,
  body.high-contrast h2 b,
  body.high-contrast h2 center,
  body.high-contrast h2 em,
  body.high-contrast h2 i,
  body.high-contrast h2 span,
  body.high-contrast h2 strong,
  body.high-contrast h3,
  body.high-contrast h3 a,
  body.high-contrast h3 a b,
  body.high-contrast h3 abbr,
  body.high-contrast h3 b,
  body.high-contrast h3 center,
  body.high-contrast h3 em,
  body.high-contrast h3 i,
  body.high-contrast h3 span,
  body.high-contrast h3 strong,
  body.high-contrast h4,
  body.high-contrast h4 a,
  body.high-contrast h4 a b,
  body.high-contrast h4 abbr,
  body.high-contrast h4 b,
  body.high-contrast h4 center,
  body.high-contrast h4 em,
  body.high-contrast h4 i,
  body.high-contrast h4 span,
  body.high-contrast h4 strong,
  body.high-contrast h5,
  body.high-contrast h5 a,
  body.high-contrast h5 a b,
  body.high-contrast h5 abbr,
  body.high-contrast h5 b,
  body.high-contrast h5 center,
  body.high-contrast h5 em,
  body.high-contrast h5 i,
  body.high-contrast h5 span,
  body.high-contrast h5 strong,
  body.high-contrast h6,
  body.high-contrast h6 a,
  body.high-contrast h6 a b,
  body.high-contrast h6 abbr,
  body.high-contrast h6 b,
  body.high-contrast h6 center,
  body.high-contrast h6 em,
  body.high-contrast h6 i,
  body.high-contrast h6 span,
  body.high-contrast h6 strong,
  body.high-contrast header,
  body.high-contrast hgroup,
  body.high-contrast html,
  body.high-contrast i,
  body.high-contrast iframe,
  body.high-contrast input,
  body.high-contrast ins,
  body.high-contrast kbd,
  body.high-contrast label,
  body.high-contrast legend,
  body.high-contrast li,
  body.high-contrast listing,
  body.high-contrast main,
  body.high-contrast mark,
  body.high-contrast marquee,
  body.high-contrast menu,
  body.high-contrast meter,
  body.high-contrast multicol,
  body.high-contrast nav:not(#toolbar),
  body.high-contrast nobr,
  body.high-contrast object,
  body.high-contrast ol,
  body.high-contrast option,
  body.high-contrast output,
  body.high-contrast p,
  body.high-contrast plaintext,
  body.high-contrast pre,
  body.high-contrast progress,
  body.high-contrast q,
  body.high-contrast rb,
  body.high-contrast rp,
  body.high-contrast rt,
  body.high-contrast ruby,
  body.high-contrast s,
  body.high-contrast samp,
  body.high-contrast section,
  body.high-contrast small,
  body.high-contrast span,
  body.high-contrast strike,
  body.high-contrast sub,
  body.high-contrast summary,
  body.high-contrast sup,
  body.high-contrast svg,
  body.high-contrast table,
  body.high-contrast tbody,
  body.high-contrast td,
  body.high-contrast text,
  body.high-contrast textarea,
  body.high-contrast th,
  body.high-contrast thead,
  body.high-contrast time,
  body.high-contrast tr,
  body.high-contrast tt,
  body.high-contrast u,
  body.high-contrast ul,
  body.high-contrast var,
  body.high-contrast video,
  body.high-contrast xmp {
    background: black !important;
    background-color: black !important;
  }
  body.high-contrast a,
  body.high-contrast article,
  body.high-contrast aside,
  body.high-contrast basefont,
  body.high-contrast bdi,
  body.high-contrast big,
  body.high-contrast blink,
  body.high-contrast blockquote,
  body.high-contrast body,
  body.high-contrast button,
  body.high-contrast canvas,
  body.high-contrast caption,
  body.high-contrast center,
  body.high-contrast code,
  body.high-contrast col,
  body.high-contrast colgroup,
  body.high-contrast command,
  body.high-contrast dd,
  body.high-contrast del,
  body.high-contrast details,
  body.high-contrast dir,
  body.high-contrast div,
  body.high-contrast dl,
  body.high-contrast dt,
  body.high-contrast embed,
  body.high-contrast fieldset,
  body.high-contrast figcaption,
  body.high-contrast figure,
  body.high-contrast font,
  body.high-contrast footer,
  body.high-contrast form,
  body.high-contrast header,
  body.high-contrast hgroup,
  body.high-contrast html,
  body.high-contrast iframe,
  body.high-contrast img,
  body.high-contrast input,
  body.high-contrast ins,
  body.high-contrast kbd,
  body.high-contrast label,
  body.high-contrast legend,
  body.high-contrast li,
  body.high-contrast listing,
  body.high-contrast main,
  body.high-contrast mark,
  body.high-contrast marquee,
  body.high-contrast menu,
  body.high-contrast meter,
  body.high-contrast multicol,
  body.high-contrast nav,
  body.high-contrast nobr,
  body.high-contrast object,
  body.high-contrast ol,
  body.high-contrast option,
  body.high-contrast output,
  body.high-contrast p,
  body.high-contrast plaintext,
  body.high-contrast pre,
  body.high-contrast progress,
  body.high-contrast q,
  body.high-contrast rb,
  body.high-contrast rp,
  body.high-contrast rt,
  body.high-contrast ruby,
  body.high-contrast s,
  body.high-contrast samp,
  body.high-contrast section,
  body.high-contrast small,
  body.high-contrast span,
  body.high-contrast strike,
  body.high-contrast sub,
  body.high-contrast summary,
  body.high-contrast sup,
  body.high-contrast svg,
  body.high-contrast table,
  body.high-contrast tbody,
  body.high-contrast td,
  body.high-contrast text,
  body.high-contrast textarea,
  body.high-contrast th,
  body.high-contrast thead,
  body.high-contrast time,
  body.high-contrast tr,
  body.high-contrast tt,
  body.high-contrast u,
  body.high-contrast ul,
  body.high-contrast var,
  body.high-contrast video,
  body.high-contrast xmp {
    color: white !important;
  }
  body.high-contrast abbr,
  body.high-contrast acronym,
  body.high-contrast b,
  body.high-contrast b span,
  body.high-contrast h1 b,
  body.high-contrast h1 strong,
  body.high-contrast h2 b,
  body.high-contrast h2 strong,
  body.high-contrast h3 b,
  body.high-contrast h3 strong,
  body.high-contrast h4 b,
  body.high-contrast h4 strong,
  body.high-contrast h5 b,
  body.high-contrast h5 strong,
  body.high-contrast h6 b,
  body.high-contrast h6 strong,
  body.high-contrast strong,
  body.high-contrast strong span {
    color: yellow !important;
  }
  body.high-contrast address,
  body.high-contrast address span,
  body.high-contrast cite,
  body.high-contrast cite span,
  body.high-contrast dfn,
  body.high-contrast dfn span,
  body.high-contrast em,
  body.high-contrast em span,
  body.high-contrast h1 em,
  body.high-contrast h1 i,
  body.high-contrast h2 em,
  body.high-contrast h2 i,
  body.high-contrast h3 em,
  body.high-contrast h3 i,
  body.high-contrast h4 em,
  body.high-contrast h4 i,
  body.high-contrast h5 em,
  body.high-contrast h5 i,
  body.high-contrast h6 em,
  body.high-contrast h6 i,
  body.high-contrast i,
  body.high-contrast i span,
  body.high-contrast u,
  body.high-contrast u span {
    color: #FFFF80 !important;
  }
  body.high-contrast dt {
    border-top: thin solid grey !important;
  }
  body.high-contrast h1,
  body.high-contrast h1 a,
  body.high-contrast h1 a b,
  body.high-contrast h1 abbr,
  body.high-contrast h1 center,
  body.high-contrast h1 span,
  body.high-contrast h2,
  body.high-contrast h2 a,
  body.high-contrast h2 a b,
  body.high-contrast h2 abbr,
  body.high-contrast h2 center,
  body.high-contrast h2 span,
  body.high-contrast h3,
  body.high-contrast h3 a,
  body.high-contrast h3 a b,
  body.high-contrast h3 abbr,
  body.high-contrast h3 center,
  body.high-contrast h3 span,
  body.high-contrast h4,
  body.high-contrast h4 a,
  body.high-contrast h4 a b,
  body.high-contrast h4 abbr,
  body.high-contrast h4 center,
  body.high-contrast h4 span,
  body.high-contrast h5,
  body.high-contrast h5 a,
  body.high-contrast h5 a b,
  body.high-contrast h5 abbr,
  body.high-contrast h5 center,
  body.high-contrast h5 span,
  body.high-contrast h6,
  body.high-contrast h6 a,
  body.high-contrast h6 a b,
  body.high-contrast h6 abbr,
  body.high-contrast h6 center,
  body.high-contrast h6 span {
    color: #40C090 !important;
  }
  body.high-contrast img {
    background: #808080 !important;
    background-color: #808080 !important;
  }
  body.high-contrast abbr,
  body.high-contrast acronym {
    border-bottom: 1px dotted !important;
  }
  body.high-contrast :focus {
    outline: thin dotted !important;
  }
  body.high-contrast a.button,
  body.high-contrast a.button abbr,
  body.high-contrast a.button acronym,
  body.high-contrast a.button b,
  body.high-contrast a.button basefont,
  body.high-contrast a.button big,
  body.high-contrast a.button br,
  body.high-contrast a.button code,
  body.high-contrast a.button div,
  body.high-contrast a.button em,
  body.high-contrast a.button font,
  body.high-contrast a.button h1,
  body.high-contrast a.button h2,
  body.high-contrast a.button h3,
  body.high-contrast a.button h4,
  body.high-contrast a.button h5,
  body.high-contrast a.button h6,
  body.high-contrast a.button i,
  body.high-contrast a.button kbd,
  body.high-contrast a.button rb,
  body.high-contrast a.button rp,
  body.high-contrast a.button rt,
  body.high-contrast a.button ruby,
  body.high-contrast a.button samp,
  body.high-contrast a.button small,
  body.high-contrast a.button span,
  body.high-contrast a.button strong,
  body.high-contrast a.button tt,
  body.high-contrast a.button u,
  body.high-contrast a.button var,
  body.high-contrast a:link,
  body.high-contrast a:link abbr,
  body.high-contrast a:link acronym,
  body.high-contrast a:link b,
  body.high-contrast a:link basefont,
  body.high-contrast a:link big,
  body.high-contrast a:link br,
  body.high-contrast a:link code,
  body.high-contrast a:link div,
  body.high-contrast a:link em,
  body.high-contrast a:link font,
  body.high-contrast a:link h1,
  body.high-contrast a:link h2,
  body.high-contrast a:link h3,
  body.high-contrast a:link h4,
  body.high-contrast a:link h5,
  body.high-contrast a:link h6,
  body.high-contrast a:link i,
  body.high-contrast a:link kbd,
  body.high-contrast a:link rb,
  body.high-contrast a:link rp,
  body.high-contrast a:link rt,
  body.high-contrast a:link ruby,
  body.high-contrast a:link samp,
  body.high-contrast a:link small,
  body.high-contrast a:link span,
  body.high-contrast a:link strong,
  body.high-contrast a:link tt,
  body.high-contrast a:link u,
  body.high-contrast a:link var,
  body.high-contrast div#secondaryNav div#documentNavigation ul.navigationTabs li.tabItem {
    color: #0080FF !important;
  }
  body.high-contrast button,
  body.high-contrast input,
  body.high-contrast textarea,
  body.high-contrast select,
  body.high-contrast table,
  body.high-contrast td,
  body.high-contrast th,
  body.high-contrast tr,
  body.high-contrast tt {
    border: 1px solid #ffffff !important;
  }
  body.high-contrast button {
    background: #600040 !important;
    background-color: #600040 !important;
  }
  body.high-contrast select {
    -webkit-appearance: listbox !important;
    background: #600060 !important;
    background-color: #600060 !important;
  }
  body.high-contrast a:visited,
  body.high-contrast a:visited abbr,
  body.high-contrast a:visited acronym,
  body.high-contrast a:visited b,
  body.high-contrast a:visited basefont,
  body.high-contrast a:visited big,
  body.high-contrast a:visited br,
  body.high-contrast a:visited code,
  body.high-contrast a:visited div,
  body.high-contrast a:visited em,
  body.high-contrast a:visited font,
  body.high-contrast a:visited h1,
  body.high-contrast a:visited h2,
  body.high-contrast a:visited h3,
  body.high-contrast a:visited h4,
  body.high-contrast a:visited h5,
  body.high-contrast a:visited h6,
  body.high-contrast a:visited i,
  body.high-contrast a:visited kbd,
  body.high-contrast a:visited rb,
  body.high-contrast a:visited rp,
  body.high-contrast a:visited rt,
  body.high-contrast a:visited ruby,
  body.high-contrast a:visited samp,
  body.high-contrast a:visited small,
  body.high-contrast a:visited span,
  body.phigh-contrast a:visited strong,
  body.high-contrast a:visited tt,
  body.high-contrast a:visited u,
  body.high-contrast a:visited var,
  body.-contrast div#secondaryNav div#documentNavigation ul.navigationTabs li.tabItem.active {
    color: #00FFFF !important;
  }
  body.high-contrast ::selection,
  body.high-contrast ::-moz-selection {
    background: #4080c0 !important;
    background-color: #4080c0 !important;
  }
  body.high-contrast a.button:hover,
  body.high-contrast a.button:hover abbr,
  body.high-contrast a.button:hover acronym,
  body.high-contrast a.button:hover b,
  body.high-contrast a.button:hover basefont,
  body.high-contrast a.button:hover big,
  body.high-contrast a.button:hover br,
  body.high-contrast a.button:hover code,
  body.high-contrast a.button:hover div,
  body.high-contrast a.button:hover em,
  body.high-contrast a.button:hover font,
  body.high-contrast a.button:hover h1,
  body.high-contrast a.button:hover h2,
  body.high-contrast a.button:hover h3,
  body.high-contrast a.button:hover h4,
  body.high-contrast a.button:hover h5,
  body.high-contrast a.button:hover h6,
  body.high-contrast a.button:hover i,
  body.high-contrast a.button:hover kbd,
  body.high-contrast a.button:hover rb,
  body.high-contrast a.button:hover rp,
  body.high-contrast a.button:hover rt,
  body.high-contrast a.button:hover ruby,
  body.high-contrast a.button:hover samp,
  body.high-contrast a.button:hover small,
  body.high-contrast a.button:hover span,
  body.high-contrast a.button:hover strong,
  body.high-contrast a.button:hover tt,
  body.high-contrast a.button:hover u,
  body.high-contrast a.button:hover var,
  body.high-contrast a:link:hover,
  body.high-contrast a:link:hover abbr,
  body.high-contrast a:link:hover acronym,
  body.high-contrast a:link:hover b,
  body.high-contrast a:link:hover basefont,
  body.high-contrast a:link:hover big,
  body.high-contrast a:link:hover br,
  body.high-contrast a:link:hover code,
  body.high-contrast a:link:hover div,
  body.high-contrast a:link:hover em,
  body.high-contrast a:link:hover font,
  body.high-contrast a:link:hover h1,
  body.high-contrast a:link:hover h2,
  body.high-contrast a:link:hover h3,
  body.high-contrast a:link:hover h4,
  body.high-contrast a:link:hover h5,
  body.high-contrast a:link:hover h6,
  body.high-contrast a:link:hover i,
  body.high-contrast a:link:hover kbd,
  body.high-contrast a:link:hover rb,
  body.high-contrast a:link:hover rp,
  body.high-contrast a:link:hover rt,
  body.high-contrast a:link:hover ruby,
  body.high-contrast a:link:hover samp,
  body.high-contrast a:link:hover small,
  body.high-contrast a:link:hover span,
  body.high-contrast a:link:hover strong,
  body.high-contrast a:link:hover tt,
  body.high-contrast a:link:hover u,
  body.high-contrast a:link:hover var,
  body.high-contrast a:visited:hover,
  body.high-contrast a:visited:hover abbr,
  body.high-contrast a:visited:hover acronym,
  body.high-contrast a:visited:hover b,
  body.high-contrast a:visited:hover basefont,
  body.high-contrast a:visited:hover big,
  body.high-contrast a:visited:hover br,
  body.high-contrast a:visited:hover code,
  body.high-contrast a:visited:hover div,
  body.high-contrast a:visited:hover em,
  body.high-contrast a:visited:hover font,
  body.high-contrast a:visited:hover h1,
  body.high-contrast a:visited:hover h2,
  body.high-contrast a:visited:hover h3,
  body.high-contrast a:visited:hover h4,
  body.high-contrast a:visited:hover h5,
  body.high-contrast a:visited:hover h6,
  body.high-contrast a:visited:hover i,
  body.high-contrast a:visited:hover kbd,
  body.high-contrast a:visited:hover rb,
  body.high-contrast a:visited:hover rp,
  body.high-contrast a:visited:hover rt,
  body.high-contrast a:visited:hover ruby,
  body.high-contrast a:visited:hover samp,
  body.high-contrast a:visited:hover small,
  body.high-contrast a:visited:hover span,
  body.high-contrast a:visited:hover strong,
  body.high-contrast a:visited:hover tt,
  body.high-contrast a:visited:hover u,
  body.high-contrast a:visited:hover var {
    background: #400000 !important;
    background-color: #400000 !important;
  }
  body.high-contrast body > input#site + div#wrapper span.mk,
  body.high-contrast body > input#site + div#wrapper span.mk b,
  body.high-contrast input[type=reset] {
    background: #400060 !important;
    background-color: #400060 !important;
  }
  body.high-contrast div[role="button"],
  body.high-contrast input[type=button],
  body.high-contrast input[type=submit] {
    background: #600040 !important;
    background-color: #600040 !important;
  }
  body.high-contrast input[type=search] {
    -webkit-appearance: textfield !important;
  }
  body.high-contrast html button[disabled],
  body.high-contrast html input[disabled],
  body.high-contrast html select[disabled],
  body.high-contrast html textarea[disabled] {
    background: #404040 !important;
    background-color: #404040 !important;
  }
  body.high-contrast .menu li a span.label {
    text-transform: none !important;
  }
  body.high-contrast .menu li a span.label,
  body.high-contrast div.jwplayer span.jwcontrolbar,
  body.high-contrast div.jwplayer span.jwcontrols {
    display: inline !important;
  }
  body.high-contrast a:link.new,
  body.high-contrast a:link.new i,
  body.high-contrast a:link.new b,
  body.high-contrast span.Apple-style-span {
    color: #FFFF40 !important;
  }
  body.high-contrast body.mediawiki img.tex {
    background: white !important;
    background-color: white !important;
    border: white solid 3px !important;
  }
  body.high-contrast text > tspan:first-letter,
  body.high-contrast text > tspan:first-line {
    background: inherit !important;
    background-color: inherit !important;
    color: inherit !important;
  }
  body.high-contrast div.sbtc div.sbsb_a li.sbsb_d div,
  body.high-contrast table.gssb_c tr.gssb_i a,
  body.high-contrast table.gssb_c tr.gssb_i b,
  body.high-contrast table.gssb_c tr.gssb_i span,
  body.high-contrast table.gssb_c tr.gssb_i td {
    background: #003050 !important;
    background-color: #003050 !important;
  }
  body.high-contrast img[width="18"][height="18"] {
    height: 18px !important;
    width: 18px !important;
  }
  body.high-contrast a > span.iconHelp:empty:after {
    content: "Help" !important;
  }
  body.high-contrast div#gmap,
  body.high-contrast div#gmap * {
    background: initial !important;
  }
   body.dark-mode {
    background: #121212 !important;
    color: #655b5b !important;
  }
  
  body.dark-mode img {
    filter: grayscale(75%) contrast(90%);
  }
  
  body.dark-mode img:hover {
    filter: grayscale(0) contrast(100%);
  } 
    body.negative-contrast {
    filter: invert(100%);
}
    .grayscale {
        filter: grayscale(100%);
    }
    .negative-contrast {
        filter: invert(100%);
    }
    .protanopia {
        filter: url(#protanopia);
    }
    .deuteranopia {
        filter: url(#deuteranopia);
    }
    .tritanopia {
        filter: url(#tritanopia);
    }
    .dyslexia-mode a, .dyslexia-mode h1, .dyslexia-mode h2, .dyslexia-mode h3, .dyslexia-mode h4, .dyslexia-mode h5, .dyslexia-mode h6 {
        text-decoration: underline;
        font-family: 'OpenDyslexic', sans-serif;
    }
    .tda-mode {
        animation: none !important;
        transition: none !important;
    }
    .accessibility-menu {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        max-width: 600px;
        margin: auto;
    }
        .accessibility-menu h3 {
        font-size: 1.5rem;
        color: #007bff;
    }
    .accessibility-menu .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .accessibility-menu .card-body {
        padding: 15px;
    }
    .accessibility-menu .btn {
        border-radius: 20px;
        transition: background-color 0.3s, color 0.3s;
    }
    .accessibility-menu .btn:hover {
        background-color: #007bff;
        color: #fff;
    }
    .accessibility-menu .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: #fff;
    }
    .accessibility-menu .btn-outline-info:hover {
        background-color: #17a2b8;
        color: #fff;
    }
    .accessibility-menu .btn-outline-warning:hover {
        background-color: #ffc107;
        color: #fff;
    }
    .accessibility-menu .btn-outline-success:hover {
        background-color: #28a745;
        color: #fff;
    }
    .accessibility-menu .btn-outline-danger:hover {
        background-color: #dc3545;
        color: #fff;
    }
    .accessibility-menu .btn-outline-primary:hover {
        background-color: #007bff;
        color: #fff;
    }
    .accessibility-menu .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: #fff;
    }
    .accessibility-menu .icon-button {
        cursor: pointer;
        transition: transform 0.3s, color 0.3s;
    }
    .accessibility-menu .icon-button:hover {
        transform: scale(1.1);
        color: #007bff;
    }
    .accessibility-menu .icon-label {
        font-size: 0.85rem;
    }
    .accessibility-menu .icon-button.selected i {
        color: #007bff;
    }
    .accessibility-menu .icon-button.selected {
        transform: scale(1.1);
    }
        #readingRuler {
            background-color: rgba(255, 255, 0, 0.8);
            position: fixed;
            width: 100%;
            height: 5px;
            z-index: 1000;
        }

        #readingMask {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            pointer-events: none;
        }

        #readingHole {
            width: 100%;
            height: 50px;
            background-color: transparent;
        }

        #magnifier {
            position: fixed;
            border-radius: 50%;
            border: 3px solid #007bff;
            overflow: hidden;
            z-index: 10000;
            pointer-events: none;
        }
          .fixed-accessibility-button {
        font-size: initial !important;
    }
    .accessibility-icon {
        font-size: 3em !important;
        color: white !important;
        pointer-events: none;
    }  
`;
document.head.appendChild(style);

loadSettings();
