<?php
    /**
     * FICHEIRO: public/aceitar_convite.php
     * OBJETIVO: Ecrã público para funcionários aceitarem convites via Token.
     * STATUS: Fase 5 - Arquitetura SaaS.
     * VERSÃO: 1.2 (Correção Crítica: Rota Hostinger Restaurada)
     * DATA DE CRIAÇÃO: 02 de Abril de 2026
     * AUTOR: Engenharia Kairós (Leon)
    */

    // ROTA CORRIGIDA: Exatamente igual ao login.php que está a funcionar no seu servidor
    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';
    
    use src\Controllers\InviteController;

    session_start();

    // Captura o Bilhete Dourado da URL (ex: ?token=abc)
    $token = $_GET['token'] ?? '';
    
    if (empty($token)) {
        die("<div style='text-align:center; padding: 50px; font-family:sans-serif;'><h1>Acesso Inválido</h1><p>Nenhum token de convite fornecido ou link quebrado.</p></div>");
    }

    $controller = new InviteController();
    $erro = '';
    
    // O Segurança verifica o bilhete antes de mostrar o formulário
    $validacao = $controller->validarToken($token);
    
    if (!$validacao['valido']) {
        die("<div style='text-align:center; padding: 50px; font-family:sans-serif;'><h1>Convite Inválido</h1><p>" . htmlspecialchars($validacao['mensagem']) . "</p><br><a href='login.php' style='color:#38bdf8; text-decoration:none; font-weight:bold;'>Ir para Login</a></div>");
    }

    $convite = $validacao['convite'];

    // Processamento do Formulário de Aceitação
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = htmlspecialchars(trim($_POST['nome'] ?? ''), ENT_QUOTES, 'UTF-8');
        $senha = $_POST['senha'] ?? '';
        $senha_confirma = $_POST['senha_confirma'] ?? '';

        if (empty($nome) || empty($senha)) {
            $erro = "Preencha todos os campos.";
        } elseif ($senha !== $senha_confirma) {
            $erro = "As palavras-passe não coincidem.";
        } elseif (strlen($senha) < 8) {
            $erro = "A palavra-passe deve ter pelo menos 8 caracteres.";
        } else {
            $resultado = $controller->processarAceitacao($token, $nome, $senha);

            if ($resultado['sucesso']) {
                $_SESSION['flash_sucesso'] = $resultado['mensagem'];
                header("Location: login.php");
                exit;
            } else {
                $erro = $resultado['mensagem'];
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kairós Connect - Aceitar Convite</title>
    <link rel="stylesheet" href="css/auth.css?v=1.0">
</head>
<body class="auth-body">
    <div class="auth-card">
        <h2>Convite de Equipa</h2>
        <div class="auth-subtitle">
            Você foi convidado(a) para aceder ao ambiente:<br>
            <strong style="color: #0284c7; font-size: 1.1rem;"><?php echo htmlspecialchars($convite['nome_empresa']); ?></strong>
        </div>

        <?php if($erro): ?>
            <div class="auth-alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="auth-form-group">
                <label style="display: block; font-size: 0.8rem; color: #64748b; margin-bottom: 5px; font-weight: 600;">E-mail Corporativo</label>
                <input type="email" class="auth-control" value="<?php echo htmlspecialchars($convite['email']); ?>" disabled style="background-color: #f1f5f9; color: #94a3b8; cursor: not-allowed; opacity: 0.8;">
            </div>
            <div class="auth-form-group">
                <input type="text" name="nome" class="auth-control" placeholder="O seu Nome Completo" required>
            </div>
            <div class="auth-form-group">
                <input type="password" name="senha" class="auth-control" placeholder="Crie uma Palavra-passe" required minlength="8">
            </div>
            <div class="auth-form-group">
                <input type="password" name="senha_confirma" class="auth-control" placeholder="Confirme a Palavra-passe" required minlength="8">
            </div>
            <button type="submit" class="auth-btn-submit">Ativar Conta e Entrar</button>
        </form>

        <div class="auth-links-footer">
            Já configurou a sua conta? <a href="login.php">Iniciar Sessão</a>
        </div>
    </div>
</body>
</html>