/**
 * PROTOCOLO KAIRÓS: Motor de Interação Frontend (Modal, Retenção & Telemetria)
 * STATUS: Limpo, Modular e Procedural
 */

// ==========================================
// 1. GESTÃO DE ROLAGEM E EVENTOS DE TELA
// ==========================================
document.addEventListener("DOMContentLoaded", function() {
    let scrollPosition = sessionStorage.getItem("kairosScrollPosition");
    if (scrollPosition !== null) {
        window.scrollTo(0, parseInt(scrollPosition));
        sessionStorage.removeItem("kairosScrollPosition"); 
    }
});

window.addEventListener("beforeunload", function() {
    sessionStorage.setItem("kairosScrollPosition", window.scrollY);
});

// ==========================================
// 2. FUNÇÕES DO MODAL (COFRE DE ATIVOS)
// ==========================================
function fecharModal() {
    document.getElementById('modalParametro').style.display = 'none';
}

function abrirModalNovo() {
    document.querySelector('#formParametro').reset();
    
    let inputChave = document.querySelector('input[name="chave"]');
    inputChave.readOnly = false;
    inputChave.style.opacity = '1';
    
    document.querySelector('textarea[name="valor"]').placeholder = "Insira o valor do ativo...";
    document.querySelector('.modal-content h2').innerText = "+ Adicionar Ativo";
    
    document.getElementById('modalParametro').style.display = 'flex';
}

function editarModal(chave, grupo, valor, isSecret) {
    document.querySelector('select[name="config_group"]').value = grupo;
    
    let inputChave = document.querySelector('input[name="chave"]');
    inputChave.value = chave;
    inputChave.readOnly = true; 
    inputChave.style.opacity = '0.6'; 
    
    let textareaValor = document.querySelector('textarea[name="valor"]');
    let checkboxSecret = document.querySelector('input[name="is_secret"]');

    if (isSecret == 1) {
        textareaValor.value = ''; 
        textareaValor.placeholder = "⚠️ Dado sensível blindado. Digite o novo valor para sobrescrever.";
        checkboxSecret.checked = true;
    } else {
        textareaValor.value = valor; 
        textareaValor.placeholder = "";
        checkboxSecret.checked = false;
    }

    document.querySelector('.modal-content h2').innerText = "✏️ Editar Ativo";
    document.getElementById('modalParametro').style.display = 'flex';
}

// ==========================================
// 3. TELEMETRIA ASSÍNCRONA (HUB CENTRAL)
// ==========================================
document.addEventListener("DOMContentLoaded", function() {
    // Trava de Segurança: Só executa se estiver na tela do Hub (se achar o 'dot-internet')
    const dotInternet = document.getElementById('dot-internet');
    
    if (dotInternet) {
        const CORES = {
            verde: '#22c55e',
            amarelo: '#eab308',
            vermelho: '#ef4444',
            cinza: '#cbd5e1'
        };

        // 3.1. Testa a Internet Local
        function atualizarInternet() {
            const dot = document.getElementById('dot-internet');
            dot.classList.remove('testando');
            dot.style.backgroundColor = navigator.onLine ? CORES.verde : CORES.vermelho;
        }
        window.addEventListener('online', atualizarInternet);
        window.addEventListener('offline', atualizarInternet);
        atualizarInternet(); 

        // 3.2. Testa os Ativos da Kairós (Ping)
        async function pingServico(servicoId) {
            const dot = document.getElementById('dot-' + servicoId);
            try {
                const response = await fetch('health_check.php?action=' + servicoId);
                const data = await response.json();
                
                dot.classList.remove('testando');
                
                if (data.status === 'Operacional') {
                    dot.style.backgroundColor = CORES.verde;
                } else if (data.status === 'Incompleto') {
                    dot.style.backgroundColor = CORES.amarelo;
                } else {
                    dot.style.backgroundColor = CORES.vermelho;
                }
            } catch (erro) {
                dot.classList.remove('testando');
                dot.style.backgroundColor = CORES.vermelho;
            }
        }

        // 3.3. Inicia Varredura
        pingServico('banco');
        pingServico('meta');
        pingServico('gemini');
    }
});