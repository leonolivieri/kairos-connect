<?php
    /**
     * =========================================================================
     * PROJETO: Kairós Connect
     * ARQUIVO: public/omnichannel.php
     * OBJETIVO: Tela de Comando Omnichannel (Vitrine Nível 1)
     * VERSÃO: 1.0.0
     * DATA/HORA: 18/03/2026 - 15:10
     * IMPLEMENTAÇÃO: Criação da interface HTML isolada, integração com o 
     * style.css global e preparação das âncoras (IDs) para injeção via JS.
     * =========================================================================
    */


    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';

        // ATIVAÇÃO TEMPORÁRIA DE DEBUG
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL)
?>

<!DOCTYPE html>

<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Omnichannel | Kairós Connect</title>
        <!-- Ativo Global da Kairós (Herdando variáveis e cores) -->
        <link rel="stylesheet" href="css/style.css">
        <!-- Ativo Específico do Layout de Duas Colunas -->
        <link rel="stylesheet" href="css/omni.css">
    </head>
    <body class="hub-theme">

        <div class="container" style="max-width: 1400px;">
            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h1 style="margin:0;">KAIRÓS <span style="color: var(--accent);">OMNI</span></h1>
                    <p style="color: var(--text-dim); margin:0; font-size: 0.9rem;">Central de Comando de Mensagens</p>
                </div>
                <a href="index.php" class="telemetria-item" style="padding: 10px 20px;">Voltar ao Hub</a>
            </header>

            <main class="omni-grid">
                <!-- COLUNA ESQUERDA: LISTA DE CONVERSAS -->
                <aside class="omni-sidebar">
                    <div class="sidebar-header">
                        Conversas Ativas
                    </div>
                    <div id="lista-contatos" class="sidebar-content">
                        <!-- Contatos serão injetados via JS -->
                    </div>
                </aside>

                <!-- COLUNA DIREITA: ÁREA DO CHAT -->
                <section class="omni-chat">
                    <div class="chat-header">
                        <h3 id="nome-contato-ativo" style="margin: 0;">Selecione uma conversa</h3>
                        <small id="status-contato-ativo" style="color: var(--text-dim);">-</small>
                    </div>
                    
                    <div id="area-mensagens" class="chat-messages">
                        <!-- Balões de mensagem serão injetados via JS -->
                        <div style="text-align: center; margin-top: 50px; color: var(--text-dim);">
                            <span style="font-size: 3rem;">💬</span>
                            <p>Aguardando seleção...</p>
                        </div>
                    </div>

                    <div class="chat-input-area">
                        <input type="text" id="input-mensagem" placeholder="Escreva sua resposta..." disabled>
                        <button id="btn-enviar-mensagem" class="btn-primary" style="margin: 0; padding: 10px 20px;" disabled>Enviar</button>
                    </div>
                </section>
            </main>
        </div>

        <!-- Motor Global da Kairós -->
        <script src="js/script.js"></script>
        <!-- Motor Específico da Tela -->
        <script src="js/omni.js"></script>
    </body>
</html>