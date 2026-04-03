<?php
    /**
     * FICHEIRO: public/escolher_workspace.php
     * OBJETIVO: Átrio de Seleção para utilizadores com múltiplos Workspaces.
     * STATUS: Fase 5.1 - Arquitetura SaaS Multi-tenant.
     * VERSÃO: 1.0 (Tapete de Escolha)
     * DATA DE CRIAÇÃO: 03 de Abril de 2026
     * AUTOR: Engenharia Kairós (Leon)
    */

    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';

    session_start();

    // Proteção de Ecrã: Só entra aqui quem está logado E tem o array temporário
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['temp_workspaces'])) {
        header("Location: login.php");
        exit;
    }

    $workspaces = $_SESSION['temp_workspaces'];
    $erro = '';

    // Processa a escolha do utilizador
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ws_id'])) {
        $selected_ws_id = (int)$_POST['ws_id'];
        $selected_ws = null;

        // Procura a empresa selecionada dentro das opções válidas do utilizador
        foreach ($workspaces as $ws) {
            if ((int)$ws['id'] === $selected_ws_id) {
                $selected_ws = $ws;
                break;
            }
        }

        if ($selected_ws) {
            // Sucesso: Monta o Crachá Oficial do Workspace
            $_SESSION['ws_id'] = $selected_ws['id'];
            $_SESSION['ws_nome'] = $selected_ws['nome_empresa'];
            $_SESSION['ws_alias'] = $selected_ws['alias'];
            $_SESSION['ws_role'] = $selected_ws['role'];

            // Limpeza: Destrói a lista temporária da memória
            unset($_SESSION['temp_workspaces']);

            // Libertação para o Hub
            header("Location: index.php");
            exit;
        } else {
            $erro = "Ambiente selecionado é inválido ou você não tem permissão.";
        }
    }
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kairós Connect - Escolher Ambiente</title>
    <link rel="stylesheet" href="css/auth.css?v=1.0">
    <style>
        .ws-card-btn {
            width: 100%;
            text-align: left;
            background: white;
            border: 2px solid #e2e8f0;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ws-card-btn:hover {
            border-color: #38bdf8;
            background: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(56, 189, 248, 0.15);
        }
        .ws-name {
            color: #0f172a;
            font-size: 1.1rem;
            font-weight: 700;
            display: block;
            margin-bottom: 4px;
        }
        .ws-id {
            color: #64748b;
            font-size: 0.8rem;
            font-family: monospace;
        }
        .ws-role-badge {
            background: #e0f2fe;
            color: #0284c7;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
        }
        .ws-arrow {
            color: #94a3b8;
            font-size: 1.2rem;
            margin-left: 15px;
            transition: color 0.2s;
        }
        .ws-card-btn:hover .ws-arrow {
            color: #38bdf8;
        }
    </style>
</head>
<body class="auth-body">
    <div class="auth-card" style="max-width: 500px; padding: 40px;">
        <h2>Selecionar Ambiente</h2>
        <div class="auth-subtitle">Olá, <strong><?php echo htmlspecialchars($_SESSION['user_nome']); ?></strong>.<br>A qual Workspace deseja aceder agora?</div>

        <?php if($erro): ?>
            <div class="auth-alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <?php foreach($workspaces as $ws): ?>
                <form method="POST" action="">
                    <input type="hidden" name="ws_id" value="<?php echo htmlspecialchars($ws['id']); ?>">
                    <button type="submit" class="ws-card-btn">
                        <div>
                            <span class="ws-name"><?php echo htmlspecialchars($ws['nome_empresa']); ?></span>
                            <span class="ws-id">Chassi: <?php echo htmlspecialchars($ws['alias']); ?></span>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <span class="ws-role-badge"><?php echo htmlspecialchars($ws['role']); ?></span>
                            <span class="ws-arrow">➔</span>
                        </div>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>

        <div class="auth-links-footer" style="margin-top: 30px;">
            Deseja sair? <a href="logout.php" style="color: #ef4444;">Encerrar Sessão</a>
        </div>
    </div>
</body>
</html>