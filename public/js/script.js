/**
 * PROTOCOLO KAIRÓS: Motor de Interação Frontend (Modal & Retenção)
 * STATUS: Limpo e Procedural
 */

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