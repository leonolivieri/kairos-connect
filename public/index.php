<?php
    /**
     * ARQUIVO: public/index.php
     * OBJETIVO: Hub Central de Navegação do Kairós Connect (Restaurado com Telemetria).
     * STATUS: Fase 3 - Visão Gerencial (Código Limpo e Desacoplado).
     * VERSÃO: 4.1 (CSS e JS extraídos para arquivos globais com Scope Isolation)
     * DATA DE CRIAÇÃO: 10 de Março de 2026
     * ÚLTIMA ALTERAÇÃO: 18 de Março de 2026
     * AUTOR: Engenharia Kairós (Leon)
    */

    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kairós OMNI - Hub Central</title>
        
        <!-- CSS Global Kairós -->
        <link rel="stylesheet" href="css/style.css?v=1.6">
    </head>
    
    <!-- A classe 'hub-theme' isola o design limpo desta tela do Dark Mode do resto do sistema -->
    <body class="hub-theme">

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
                <a href="omnichannel.php" class="hub-card" style="border-left: 4px solid #22c55e;">
                    <span class="icon">💬</span>
                    <h3>OmniChannel</h3>
                    <p>Central de Atendimento. Triagem via IA e transbordo para Agentes.</p>
                </a>
            </div>
        </div>

        <!-- SCRIPT GLOBAL (Contém a Lógica de Telemetria e Modais) -->
        <script src="js/script.js?v=1.6"></script>
    </body>
</html>