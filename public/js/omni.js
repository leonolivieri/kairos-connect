/**
 * =========================================================================
 * PROJETO: Kairós Connect
 * ARQUIVO: public/js/omni.js
 * OBJETIVO: Motor de Interação da Vitrine Omnichannel
 * VERSÃO: 1.7.1 (Rollback de Estabilidade - Motor Polling)
 * CRIADO EM: 20/03/2026 - 20:12
 * ATUALIZADO EM: 22/03/2026 - 10:15
 * IMPLEMENTAÇÃO: Reversão tática para o motor de varredura via fetch(). 
 * O objetivo é reestabelecer a integridade visual da interface (DOM) 
 * contornando a falha de syntax error gerada pelas quebras de linha (\n) 
 * inerentes ao protocolo SSE. Mantém o disparo validado via Meta API.
 * =========================================================================
 */

document.addEventListener("DOMContentLoaded", function() {
    
    // ---------------------------------------------------------
    // MAPEAMENTO DE ÂNCORAS DO DOM
    // ---------------------------------------------------------
    const DOM = {
        listaContatos: document.getElementById('lista-contatos'),
        areaMensagens: document.getElementById('area-mensagens'),
        nomeContato: document.getElementById('nome-contato-ativo'),
        statusContato: document.getElementById('status-contato-ativo'),
        inputMsg: document.getElementById('input-mensagem'),
        btnEnviar: document.getElementById('btn-enviar-mensagem')
    };

    let contatoAtivoId = null;
    let qtdMensagensAtuais = 0; 
    let mapaMensagens = {};     
    let contatosEmAlerta = new Set(); 

    // ---------------------------------------------------------
    // PERMISSÕES E ALERTAS NATIVOS
    // ---------------------------------------------------------
    if ("Notification" in window && Notification.permission !== "granted" && Notification.permission !== "denied") {
        Notification.requestPermission();
    }

    function tocarBipe() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gainNode = ctx.createGain();
            osc.connect(gainNode);
            gainNode.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            gainNode.gain.setValueAtTime(0.1, ctx.currentTime);
            osc.start();
            gainNode.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + 0.3);
            osc.stop(ctx.currentTime + 0.3);
        } catch(e) {}
    }

    function dispararNotificacaoNativa(nome, texto) {
        if ("Notification" in window && Notification.permission === "granted") {
            if (!document.hasFocus()) {
                new Notification("Kairós Connect: " + nome, {
                    body: texto.length > 40 ? texto.substring(0, 40) + "..." : texto,
                    requireInteraction: false
                });
                tocarBipe(); 
            } else {
                tocarBipe();
            }
        } else {
            tocarBipe();
        }
    }

    // ---------------------------------------------------------
    // MOTOR DE SINCRONIZAÇÃO (SHORT POLLING)
    // ---------------------------------------------------------
    function carregarDados(modoSilencioso = false) {
        fetch('/api_omni.php')
            .then(response => response.json())
            .then(data => {
                if (data.dados && data.dados.length > 0) {
                    
                    data.dados.forEach(contato => {
                        let qtdAntiga = mapaMensagens[contato.id] || 0;
                        let qtdNova = contato.mensagens ? contato.mensagens.length : 0;

                        if (qtdNova > qtdAntiga) {
                            mapaMensagens[contato.id] = qtdNova;

                            if (qtdAntiga > 0) {
                                let teveMensagemCliente = false;
                                let textoAlerta = "";

                                for (let i = qtdAntiga; i < qtdNova; i++) {
                                    if (contato.mensagens[i].tipo === 'cliente') {
                                        teveMensagemCliente = true;
                                        textoAlerta = contato.mensagens[i].texto;
                                    }
                                }

                                if (teveMensagemCliente) {
                                    dispararNotificacaoNativa(contato.nome, textoAlerta);
                                    if (contato.id !== contatoAtivoId) {
                                        contatosEmAlerta.add(contato.id);
                                    }
                                }
                            }
                        }
                    });

                    renderizarContatos(data.dados);

                    if (contatoAtivoId) {
                        const contatoAtualizado = data.dados.find(c => c.id === contatoAtivoId);
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

    // ---------------------------------------------------------
    // RENDERIZADOR VISUAL DA VITRINE (UI)
    // ---------------------------------------------------------
    function renderizarContatos(contatos) {
        DOM.listaContatos.innerHTML = '';
        
        contatos.forEach(contato => {
            const card = document.createElement('div');
            card.className = 'contato-card';
            
            if (contato.id === contatoAtivoId) card.classList.add('active');
            if (contatosEmAlerta.has(contato.id)) card.classList.add('alerta-nova-msg');
            
            const wppIcon = `<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.82 9.82 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>`;

            card.innerHTML = `
                <div class="card-icon">${wppIcon}</div>
                <div class="card-info">
                    <h4>${contato.nome}</h4>
                    <p>${contato.telefone}</p>
                </div>
                <div class="status-unread"></div>
            `;
            
            card.addEventListener('click', () => {
                contatosEmAlerta.delete(contato.id);
                contatoAtivoId = contato.id;
                
                document.querySelectorAll('.contato-card').forEach(c => {
                    c.classList.remove('active');
                    c.classList.remove('alerta-nova-msg');
                });
                
                card.classList.add('active');
                abrirChat(contato, false); 
            });

            DOM.listaContatos.appendChild(card);
        });
    }

    function abrirChat(contato, atualizacaoAutomatica = false) {
        qtdMensagensAtuais = contato.mensagens ? contato.mensagens.length : 0;

        if (!atualizacaoAutomatica) {
            DOM.nomeContato.innerText = contato.nome;
            DOM.statusContato.innerText = contato.telefone; 
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
            
            DOM.areaMensagens.scrollTop = DOM.areaMensagens.scrollHeight;
        }
    }

    // ---------------------------------------------------------
    // MOTOR DE TRANSBORDO (ENVIO VIA META API)
    // ---------------------------------------------------------
    function enviarMensagem() {
        const texto = DOM.inputMsg.value.trim();
        
        if (!texto || !contatoAtivoId) return;

        // Trava de segurança da interface
        DOM.inputMsg.disabled = true;
        DOM.btnEnviar.disabled = true;

        fetch('/api_enviar_msg.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                telefone: contatoAtivoId,
                texto: texto
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data._metadata.status === 'sucesso') {
                DOM.inputMsg.value = ''; 
                carregarDados(true);     
            } else {
                console.error("Erro no envio:", data._metadata.mensagem);
                alert("Falha ao enviar: " + data._metadata.mensagem); 
            }
        })
        .catch(erro => {
            console.error("Falha na requisição:", erro);
        })
        .finally(() => {
            // Liberação da interface pós-processamento
            DOM.inputMsg.disabled = false;
            DOM.btnEnviar.disabled = false;
            DOM.inputMsg.focus();
        });
    }

    // ---------------------------------------------------------
    // ESCUTADORES DE EVENTOS (LISTENERS)
    // ---------------------------------------------------------
    DOM.btnEnviar.addEventListener('click', enviarMensagem);
    
    DOM.inputMsg.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') enviarMensagem();
    });

    // ---------------------------------------------------------
    // IGNIÇÃO DA ARQUITETURA
    // ---------------------------------------------------------
    carregarDados();

    setInterval(() => {
        carregarDados(true);
    }, 3000);

});