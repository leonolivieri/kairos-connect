<?php
    /**
     * FICHEIRO: public/login.php
     * OBJETIVO: Ecrã de Autenticação (View conectada ao AuthController).
     * STATUS: Fase 5.1 - Arquitetura SaaS MVC (Seleção de Contexto).
     * VERSÃO: 3.4 (Desvio Inteligente para múltiplos Workspaces)
     * DATA DE CRIAÇÃO: 01 de Abril de 2026
     * ÚLTIMA ALTERAÇÃO: 03 de Abril de 2026
     * AUTOR: Engenharia Kairós (Leon)
    */

    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';

    use src\Controllers\AuthController;

    session_start();

    if (isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }

    $erro = '';
    $sucesso = '';

    if (isset($_SESSION['flash_sucesso'])) {
        $sucesso = $_SESSION['flash_sucesso'];
        unset($_SESSION['flash_sucesso']); 
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = strtolower(trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)));
        $senha = $_POST['senha'] ?? '';

        if ($email && $senha) {
            
            $controller = new AuthController();
            $resultado = $controller->tentarLogin($email, $senha);

            if ($resultado['sucesso']) {
                // Guarda a identidade do Humano
                $_SESSION['user_id'] = $resultado['user']['id'];
                $_SESSION['user_nome'] = $resultado['user']['nome'];
                
                $total_workspaces = count($resultado['workspaces']);

                if ($total_workspaces === 0) {
                    // Caso anómalo: Humano sem empresa nenhuma
                    $erro = "A sua conta não tem acesso a nenhum ambiente corporativo.";
                    unset($_SESSION['user_id']); // Destrói a sessão para segurança
                } 
                elseif ($total_workspaces === 1) {
                    // Fluxo Padrão: Apenas 1 empresa, entra direto
                    $ws_unico = $resultado['workspaces'][0];
                    $_SESSION['ws_id'] = $ws_unico['id'];
                    $_SESSION['ws_nome'] = $ws_unico['nome_empresa'];
                    $_SESSION['ws_alias'] = $ws_unico['alias'];
                    $_SESSION['ws_role'] = $ws_unico['role'];

                    header("Location: index.php");
                    exit;
                } 
                else {
                    // Fluxo Multi-tenant: Humano tem 2 ou mais empresas
                    // Guardamos os workspaces na sessão temporária e reencaminhamos para o Átrio
                    $_SESSION['temp_workspaces'] = $resultado['workspaces'];
                    header("Location: escolher_workspace.php");
                    exit;
                }
            } else {
                $erro = $resultado['mensagem'];
            }
        } else {
            $erro = "Preencha todos os campos.";
        }
    }
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kairós Connect - Autenticação</title>
    <link rel="stylesheet" href="css/auth.css?v=1.0">
</head>
<body class="auth-body">
    <div class="auth-card">
        <h2>KAIRÓS CONNECT</h2>
        <div class="auth-subtitle">Aceda ao seu ecossistema</div>

        <?php if($sucesso): ?>
            <div class="auth-alert alert-success"><?php echo htmlspecialchars($sucesso); ?></div>
        <?php endif; ?>

        <?php if($erro): ?>
            <div class="auth-alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="auth-form-group">
                <input type="email" name="email" class="auth-control" placeholder="O seu E-mail" required style="text-transform: lowercase;">
            </div>
            <div class="auth-form-group">
                <input type="password" name="senha" class="auth-control" placeholder="A sua Senha" required>
            </div>
            <button type="submit" class="auth-btn-submit">Aceder ao Ecossistema</button>
        </form>

        <div class="auth-links-footer">
            Ainda não tem conta? <a href="cadastro.php">Registe-se aqui</a>
        </div>
    </div>
</body>
</html>