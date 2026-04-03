<?php
    /**
     * FICHEIRO: public/index.php
     * OBJETIVO: Hub Central de Navegação do Kairós Connect.
     * STATUS: Fase 6 - Vetor A (Evolução UI/UX).
     * VERSÃO: 6.1 (Código Limpo - CSS externo no style.css)
     * DATA DE CRIAÇÃO: 10 de Março de 2026
     * ÚLTIMA ALTERAÇÃO: 03 de Abril de 2026
     * AUTOR: Engenharia Kairós (Leon)
    */

    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';

    // Helper Kairós: Extrair a primeira letra do nome para o Avatar
    $primeira_letra = strtoupper(substr($_SESSION['user_nome'] ?? 'O', 0, 1));
?>

<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kairós OMNI - Hub Central</title>
        <!-- Força o recarregamento do CSS limpo (Anti-Cache) -->
        <link rel="stylesheet" href="css/style.css?v=1.8">
    </head>
    
    <body class="hub-theme">

        <!-- GLOBAL TOP BAR -->
        <?php if(isset($_SESSION['ws_id'])): ?>
        <div class="context-bar-global" style="background: #0f172a; border-bottom: 2px solid #38bdf8; padding: 10px 5%; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; box-sizing: border-box;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <span style="color: #94a3b8; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Ambiente Ativo:</span>
                <strong style="color: #f8fafc; font-size: 1rem;"><?php echo htmlspecialchars($_SESSION['ws_nome']); ?></strong>
                <span style="background: #38bdf8; color: #0f172a; font-size: 0.65rem; padding: 3px 8px; border-radius: 4px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                    <?php echo htmlspecialchars($_SESSION['ws_role']); ?>
                </span>
            </div>
            <div style="display: none; @media (min-width: 600px) { display: block; } text-align: right;">
                <span style="color: #64748b; font-size: 0.75rem; margin-right: 8px;">ID da Instância (Chassi):</span>
                <span style="color: #cbd5e1; font-family: monospace; font-size: 0.9rem; letter-spacing: 1px;"><?php echo htmlspecialchars($_SESSION['ws_alias']); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <div class="container" style="margin-top: 30px;">
            
            <!-- HEADER MODERNO -->
            <header style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                <div>
                    <h1>KAIRÓS CONNECT</h1>
                    <p>Middleware de Integração de IA e Doutrina Operacional</p>
                </div>
                
                <!-- MENU AVATAR KAIRÓS -->
                <div class="avatar-container" id="kAvatarMenu" onclick="toggleDropdown()">
                    <!-- A Foto (Bolinha com Iniciais) -->
                    <div class="avatar-circle">
                        <?php echo $primeira_letra; ?>
                    </div>
                    
                    <!-- A Caixa Suspensa -->
                    <div class="avatar-dropdown" id="kDropdown">
                        <div class="dropdown-header">
                            <span class="dropdown-header-name"><?php echo htmlspecialchars($_SESSION['user_nome']); ?></span>
                            <span class="dropdown-header-role">ID Operador: #<?php echo htmlspecialchars($_SESSION['user_id']); ?></span>
                        </div>
                        
                        <a href="#" class="dropdown-item">
                            <span class="dd-icon">👤</span> Meu Perfil
                        </a>
                        
                        <a href="trocar_workspace.php" class="dropdown-item">
                            <span class="dd-icon">⇄</span> Trocar Ambiente
                        </a>
                        
                        <a href="logout.php" class="dropdown-item dropdown-item-danger">
                            <span class="dd-icon">✖</span> Encerrar Sessão
                        </a>
                    </div>
                </div>
            </header>

            <div class="telemetria-container" style="margin-top: 20px;">
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

            <div class="hub-grid">
                
                <?php if(isset($_SESSION['ws_role']) && ($_SESSION['ws_role'] === 'OWNER' || $_SESSION['ws_role'] === 'ADMIN')): ?>
                <a href="config_workspace.php" class="hub-card" style="border-left: 4px solid #8b5cf6;">
                    <span class="icon">👥</span>
                    <h3>Gestão da Equipa</h3>
                    <p>Convites, controlo de acessos e membros do Workspace.</p>
                </a>
                <?php endif; ?>

                <a href="admin_configs.php" class="hub-card">
                    <span class="icon">⚙️</span>
                    <h3>Gestão de Ativos</h3>
                    <p>Cofre de chaves API, Tokens e Variáveis Vitais.</p>
                </a>

                <a href="gestao_prompts.php" class="hub-card" style="border-left: 4px solid #00a8ff;">
                    <span class="icon">🧠</span>
                    <h3>Biblioteca de Prompts</h3>
                    <p>Centro de Controlo Cognitivo da IA.</p>
                </a>

                <a href="omnichannel.php" class="hub-card" style="border-left: 4px solid #22c55e;">
                    <span class="icon">💬</span>
                    <h3>OmniChannel</h3>
                    <p>Central de Atendimento. Triagem via IA e transbordo para Agentes.</p>
                </a>
                
                <a href="#" class="hub-card" style="opacity: 0.5; cursor: not-allowed; background-color: #f8fafc;">
                    <span class="icon">📊</span>
                    <h3>Logs e Auditoria</h3>
                    <p>Módulo de leitura de eventos (Em Desenvolvimento).</p>
                </a>
            </div>
        </div>

        <!-- JAVASCRIPT DO DROPDOWN (Puro, limpo e direto) -->
        <script>
            function toggleDropdown() {
                document.getElementById("kDropdown").classList.toggle("show");
            }

            window.onclick = function(event) {
                if (!event.target.matches('.avatar-circle') && !event.target.matches('.avatar-container')) {
                    var dropdowns = document.getElementsByClassName("avatar-dropdown");
                    for (var i = 0; i < dropdowns.length; i++) {
                        var openDropdown = dropdowns[i];
                        if (openDropdown.classList.contains('show')) {
                            openDropdown.classList.remove('show');
                        }
                    }
                }
            }
        </script>
        
        <script src="js/script.js?v=1.6"></script>
    </body>
</html>