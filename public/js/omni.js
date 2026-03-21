/**
 * =========================================================================
 * PROJETO: Kairós Connect
 * ARQUIVO: public/js/omni.js
 * OBJETIVO: Motor de Interação da Vitrine Omnichannel
 * VERSÃO: 1.2.0 (Atualização de Rota)
 * DATA/HORA: 20/03/2026 - 20:12
 * IMPLEMENTAÇÃO: Alteração do fetch() de 'data/conversas.json' para 'api_omni.php'
 * ligando a interface à base de dados real através do OmniController.
 * =========================================================================
 */

document.addEventListener("DOMContentLoaded", function() {
    
    const DOM = {
        listaContatos: document.getElementById('lista-contatos'),
        areaMensagens: document.getElementById('area-mensagens'),
        nomeContato: document.getElementById('nome-contato-ativo'),
        statusContato: document.getElementById('status-contato-ativo'),
        inputMsg: document.getElementById('input-mensagem'),
        btnEnviar: document.getElementById('btn-enviar-mensagem')
    };

    // Variáveis de Estado (Memória da Tela)
    let contatoAtivoId = null;
    let qtdMensagensAtuais = 0;

    function carregarDados(modoSilencioso = false) {
        fetch('api_omni.php')
            .then(response => response.json())
            .then(data => {
                if (data.dados && data.dados.length > 0) {
                    renderizarContatos(data.dados);

                    // Inteligência de Atualização do Chat Aberto
                    if (contatoAtivoId) {
                        const contatoAtualizado = data.dados.find(c => c.id === contatoAtivoId);
                        
                        // Só recarrega a coluna direita se chegou mensagem nova
                        if (contatoAtualizado && contatoAtualizado.mensagens.length > qtdMensagensAtuais) {
                            abrirChat(contatoAtualizado, true);
                        }
                    }
                } else if (!modoSilencioso) {
                    DOM.listaContatos.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--text-dim);">Nenhuma conversa encontrada.</div>';
                }
            })
            .catch(erro => console.error("Erro no motor de sincronização:", erro));
    }

    function renderizarContatos(contatos) {
        DOM.listaContatos.innerHTML = '';
        
        contatos.forEach(contato => {
            const card = document.createElement('div');
            card.className = 'contato-card';
            
            // Retenção de Estado: Mantém azul o contato que estava selecionado
            if (contato.id === contatoAtivoId) {
                card.classList.add('active');
            }
            
            // Renderiza o NOME real no frontend
            card.innerHTML = `
                <h4>${contato.nome}</h4>
                <p>${contato.ultima_mensagem}</p>
            `;
            
            card.addEventListener('click', () => {
                contatoAtivoId = contato.id;
                document.querySelectorAll('.contato-card').forEach(c => c.classList.remove('active'));
                card.classList.add('active');
                abrirChat(contato, false);
            });

            DOM.listaContatos.appendChild(card);
        });
    }

    function abrirChat(contato, atualizacaoAutomatica = false) {
        // Atualiza a memória de quantidade de mensagens
        qtdMensagensAtuais = contato.mensagens ? contato.mensagens.length : 0;

        // Se for um clique manual do usuário, atualiza o cabeçalho
        if (!atualizacaoAutomatica) {
            DOM.nomeContato.innerText = contato.nome;
            DOM.statusContato.innerText = 'Ativo';
            DOM.inputMsg.disabled = false;
            DOM.btnEnviar.disabled = false;
        }

        DOM.areaMensagens.innerHTML = '';
        
        if (contato.mensagens && contato.mensagens.length > 0) {
            contato.mensagens.forEach(msg => {
                const balao = document.createElement('div');
                balao.className = `balao balao-${msg.tipo}`; 
                balao.innerHTML = `
                    ${msg.texto}
                    <span class="hora-msg">${msg.hora || ''}</span>
                `;
                DOM.areaMensagens.appendChild(balao);
            });
            
            // Desce a barra de rolagem sempre que carregar ou chegar mensagem nova
            DOM.areaMensagens.scrollTop = DOM.areaMensagens.scrollHeight;
        }
    }

    // Ignição: Primeira carga
    carregarDados();

    // POLO MAGNÉTICO (Tempo Real): Sincroniza a cada 3 segundos sem piscar a tela
    setInterval(() => {
        carregarDados(true);
    }, 3000);

});