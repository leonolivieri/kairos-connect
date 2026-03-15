<?php
/**
 * ARQUIVO: public/index.php
 * OBJETIVO: Hub Central de Navegação do Kairós Connect (Restaurado com Telemetria).
 * STATUS: Fase 3 - Visão Gerencial.
 * VERSÃO: 4.0 (Design Clássico Preservado + Micro-Dashboard Assíncrono)
 * DATA DE CRIAÇÃO: 10 de Março de 2026
 * ÚLTIMA ALTERAÇÃO: 14 de Março de 2026, 17:10 (BRT)
 * AUTOR: Engenharia Kairós (Leon)
*/

require_once __DIR__ . '/../../kairos-connect/bootstrap.php';
?>

<!DOCTYPE html>

<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kairós Connect - Hub Central</title>
        
        <!-- CSS Original Kairós (Preservado Intacto) -->
        <link rel="stylesheet" href="css/style.css?v=1.5">

        <style>
            /* ========================================================
            CSS NATIVO KAIRÓS (SEM TAILWIND)
            Garante que o design "bonitinho" original nunca quebre.
            ======================================================== */
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; }
            .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
            
            header { text-align: center; margin-bottom: 30px; }
            header h1 { font-size: 2.2rem; margin-bottom: 5px; color: #1e293b; letter-spacing: -0.5px; }
            header p { color: #64748b; font-size: 1rem; margin: 0; }

            /* Estilo da Telemetria (Bolinhas) */
            .telemetria-container { display: flex; justify-content: center; gap: 20px; margin-bottom: 40px; flex-wrap: wrap; }
            .telemetria-item { 
                display: flex; align-items: center; gap: 8px; 
                background: white; padding: 8px 16px; border-radius: 50px; 
                border: 1px solid #e2e8f0; text-decoration: none; color: #475569; 
                font-size: 0.85rem; font-weight: 600; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .telemetria-item:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
            .bolinha { width: 12px; height: 12px; border-radius: 50%; background-color: #cbd5e1; display: inline-block; transition: background-color 0.5s ease; }
            
            /* Animação de Carregamento (Pulsar) */
            @keyframes pulso { 0% { opacity: 0.5; } 50% { opacity: 1; } 100% { opacity: 0.5; } }
            .testando { animation: pulso 1.5s infinite; }

            /* Divisória */
            hr.divisor { border: 0; border-top: 1px dashed #cbd5e1; margin: 0 0 40px 0; }

            /* Menu Clássico Kairós */
            .hub-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
            .hub-card { 
                background: white; border: 1px solid #e1e8ed; border-radius: 12px; 
                padding: 30px 25px; text-decoration: none; color: #0b1f2d; 
                transition: all 0.3s ease; display: flex; flex-direction: column; 
                justify-content: center; align-items: center; text-align: center; min-height: 160px;
            }
            .hub-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.06); border-color: #8bb1c4; }
            .hub-card h3 { margin: 0 0 10px 0; font-size: 1.25rem; color: #1e293b; }
            .hub-card p { margin: 0; font-size: 0.9rem; color: #64748b; line-height: 1.4; }
            .icon { font-size: 2.5rem; margin-bottom: 15px; }
        </style>
    </head>
    <body>

        <div class="container">
            
            <header>
                <h1>KAIRÓS CONNECT</h1>
                <p>Middleware de Integração de IA e Doutrina Operacional</p>
            </header>

            <!-- MICRO-DASHBOARD DE TELEMETRIA (As "Bolinhas") -->
            <div class="telemetria-container">
                <a href="health_check.php" class="telemetria-item" title="Clique para abrir o Raio-X">
                    <span class="bolinha testando" id="dot-internet"></span> Internet
                </a>
                <a href="health_check.php" class="telemetria-item" title="Clique para abrir o Raio-X">
                    <span class="bolinha testando" id="dot-banco"></span> Cofre (BD)
                </a>
                <a href="health_check.php" class="telemetria-item" title="Clique para abrir o Raio-X">
                    <span class="bolinha testando" id="dot-meta"></span> Meta
                </a>
                <a href="health_check.php" class="telemetria-item" title="Clique para abrir o Raio-X">
                    <span class="bolinha testando" id="dot-gemini"></span> Motor IA
                </a>
            </div>

            <hr class="divisor">

            <!-- O MENU CLÁSSICO DE NAVEGAÇÃO -->
            <div class="hub-grid">
                <a href="admin_configs.php" class="hub-card">
                    <span class="icon">⚙️</span>
                    <h3>Gestão de Ativos</h3>
                    <p>Cofre de chaves API, Tokens e Variáveis Vitais da Hostinger.</p>
                </a>

                <a href="gestao_prompts.php" class="hub-card" style="border-left: 4px solid #00a8ff;">
                    <span class="icon">🧠</span>
                    <h3>Biblioteca de Prompts</h3>
                    <p>Centro de Controlo Cognitivo. Altere a personalidade da IA.</p>
                </a>

                <a href="#" class="hub-card" style="opacity: 0.5; cursor: not-allowed; background-color: #f8fafc;">
                    <span class="icon">📊</span>
                    <h3>Logs e Auditoria</h3>
                    <p>Módulo de leitura de eventos (Em Desenvolvimento).</p>
                </a>
            </div>
        </div>

        <!-- SCRIPT DE INTELIGÊNCIA ASSÍNCRONA -->
        <script>
            const CORES = {
                verde: '#22c55e',
                amarelo: '#eab308',
                vermelho: '#ef4444',
                cinza: '#cbd5e1'
            };

            // 1. Testa a Internet Local em Tempo Real
            function atualizarInternet() {
                const dot = document.getElementById('dot-internet');
                dot.classList.remove('testando');
                dot.style.backgroundColor = navigator.onLine ? CORES.verde : CORES.vermelho;
            }
            window.addEventListener('online', atualizarInternet);
            window.addEventListener('offline', atualizarInternet);
            atualizarInternet(); // Executa ao abrir

            // 2. Testa os Ativos da Kairós silenciosamente no fundo
            async function pingServico(servicoId) {
                const dot = document.getElementById('dot-' + servicoId);
                try {
                    // Chama a nossa tela de Raio-X em modo invisível
                    const response = await fetch('health_check.php?action=' + servicoId);
                    const data = await response.json();
                    
                    dot.classList.remove('testando');
                    
                    // Mapeia o status de texto para a cor exata da bolinha
                    if (data.status === 'Operacional') {
                        dot.style.backgroundColor = CORES.verde;
                    } else if (data.status === 'Incompleto') {
                        dot.style.backgroundColor = CORES.amarelo;
                    } else {
                        dot.style.backgroundColor = CORES.vermelho;
                    }
                } catch (erro) {
                    // Se a API não responder, é falha crítica
                    dot.classList.remove('testando');
                    dot.style.backgroundColor = CORES.vermelho;
                }
            }

            // Inicia a varredura assim que a página carrega
            window.onload = function() {
                pingServico('banco');
                pingServico('meta');
                pingServico('gemini');
            };
        </script>
    </body>
</html>