<?php
    /**
     * FICHEIRO: public/config_workspace.php
     * OBJETIVO: Interface Visual para Gestão do Workspace (Padrão MVC).
     * STATUS: Fase 5 - Arquitetura de Convites SaaS.
     * VERSÃO: 2.0 (Código Limpo - Lógica transferida para WorkspaceController)
     * DATA DE CRIAÇÃO: 02 de Abril de 2026
     * AUTOR: Engenharia Kairós (Leon)
    */

    // Caminho corrigido para o Autoloader (Dois andares acima: public -> raiz)
    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';
    
    use src\Controllers\WorkspaceController;

    session_start();

    // 1. Bloqueios de Segurança (Front Controller)
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['ws_id'])) {
        header("Location: login.php");
        exit;
    }

    if ($_SESSION['ws_role'] !== 'OWNER' && $_SESSION['ws_role'] !== 'ADMIN') {
        die("<h1>Acesso Negado</h1><p>Apenas Administradores têm permissão para aceder a esta área.</p>");
    }

    $erro = '';
    $sucesso = '';
    $link_magico = '';

    // 2. Comunicação com o Controlador (A Cozinha)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'convidar') {
        $email = strtolower(trim($_POST['email_convite']));
        $cargo = $_POST['cargo_convite'] ?? 'VIEWER';
        $ws_id = $_SESSION['ws_id'];

        $controller = new WorkspaceController();
        $resultado = $controller->gerarConvite($ws_id, $email, $cargo);

        if ($resultado['sucesso']) {
            $sucesso = $resultado['mensagem'];
            $link_magico = $resultado['link'];
        } else {
            $erro = $resultado['mensagem'];
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão da Equipa - <?php echo htmlspecialchars($_SESSION['ws_nome']); ?></title>
    <link rel="stylesheet" href="css/style.css?v=1.6">
</head>
<body class="hub-theme">

    <!-- GLOBAL TOP BAR -->
    <div class="context-bar-global" style="background: #0f172a; border-bottom: 2px solid #38bdf8; padding: 10px 5%; display: flex; justify-content: space-between; align-items: center; width: 100%; box-sizing: border-box;">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <span style="color: #94a3b8; font-size: 0.75rem; text-transform: uppercase;">Ambiente:</span>
            <strong style="color: #f8fafc; font-size: 1rem;"><?php echo htmlspecialchars($_SESSION['ws_nome']); ?></strong>
            <span style="background: #38bdf8; color: #0f172a; font-size: 0.65rem; padding: 3px 8px; border-radius: 4px; font-weight: 800; text-transform: uppercase;">
                <?php echo htmlspecialchars($_SESSION['ws_role']); ?>
            </span>
        </div>
        <div>
            <a href="index.php" style="color: #38bdf8; text-decoration: none; font-size: 0.85rem; font-weight: 600;">← Voltar ao Hub</a>
        </div>
    </div>

    <div class="container" style="margin-top: 40px; max-width: 800px;">
        
        <header style="text-align: left; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 30px;">
            <h1 style="color: #1e293b; font-size: 2rem; margin: 0;">Gestão da Equipa</h1>
            <p style="color: #64748b; margin-top: 5px;">Convide funcionários e parceiros para o seu Workspace.</p>
        </header>

        <!-- CARTÃO DE CONVITE -->
        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <h3 style="margin-top: 0; color: #0f172a;">Convidar Novo Membro</h3>
            <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 20px;">O sistema gerará um Link Mágico (Token) seguro para enviar ao convidado.</p>

            <?php if($erro): ?>
                <div style="background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 600;">
                    <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>

            <form method="POST" style="display: flex; gap: 15px; flex-wrap: wrap;">
                <input type="hidden" name="acao" value="convidar">
                
                <div style="flex: 2; min-width: 250px;">
                    <label style="display: block; font-size: 0.8rem; color: #64748b; margin-bottom: 5px; font-weight: 600;">E-mail do Funcionário</label>
                    <input type="email" name="email_convite" required placeholder="ex: joao@empresa.com" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                </div>

                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; font-size: 0.8rem; color: #64748b; margin-bottom: 5px; font-weight: 600;">Nível de Acesso (Cargo)</label>
                    <select name="cargo_convite" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; background: white;">
                        <option value="ADMIN">Administrador</option>
                        <option value="EDITOR">Editor / Gestor</option>
                        <option value="VIEWER" selected>Operador (Apenas Leitura/Ação)</option>
                    </select>
                </div>

                <div style="display: flex; align-items: flex-end;">
                    <button type="submit" style="background: #38bdf8; color: #0f172a; border: none; padding: 10px 20px; font-weight: 700; border-radius: 6px; cursor: pointer; height: 40px;">
                        Gerar Link
                    </button>
                </div>
            </form>

            <!-- EXIBIÇÃO DO LINK MÁGICO APÓS O SUCESSO -->
            <?php if($sucesso && $link_magico): ?>
                <div style="margin-top: 30px; background: #f8fafc; border: 1px dashed #38bdf8; padding: 20px; border-radius: 8px;">
                    <strong style="color: #10b981; display: block; margin-bottom: 10px;">✓ <?php echo htmlspecialchars($sucesso); ?></strong>
                    
                    <label style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Link Mágico Exclusivo (Válido por 48h):</label>
                    <div style="display: flex; gap: 10px; margin-top: 5px;">
                        <input type="text" id="inputLink" value="<?php echo htmlspecialchars($link_magico); ?>" readonly style="flex: 1; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #e2e8f0; color: #0f172a; font-family: monospace;">
                        <button onclick="copiarLink()" style="background: #0f172a; color: white; border: none; padding: 0 15px; border-radius: 6px; cursor: pointer; font-weight: 600;">Copiar</button>
                    </div>
                </div>

                <script>
                    function copiarLink() {
                        var input = document.getElementById("inputLink");
                        input.select();
                        input.setSelectionRange(0, 99999);
                        navigator.clipboard.writeText(input.value).then(function() {
                            alert("Link copiado para a área de transferência!");
                        });
                    }
                </script>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>