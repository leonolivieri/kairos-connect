<?php
    /**
     * FICHEIRO: public/cadastro.php
     * OBJETIVO: Ecrã de Registo B2B (View conectada ao RegisterController).
     * STATUS: Fase 5 - Arquitetura SaaS MVC.
     * VERSÃO: 3.0 (Código Limpo MVC e Caminhos Corrigidos)
     * DATA DE CRIAÇÃO: 01 de Abril de 2026
     * ÚLTIMA ALTERAÇÃO: 02 de Abril de 2026
     * AUTOR: Engenharia Kairós (Leon)
    */

    require_once __DIR__ . '/../../bootstrap.php';

    use src\Controllers\RegisterController;

    session_start();
    if (isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }

    $erro = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = htmlspecialchars(trim($_POST['nome'] ?? ''), ENT_QUOTES, 'UTF-8');
        $email = strtolower(trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)));
        $senha = $_POST['senha'] ?? '';
        $senha_confirma = $_POST['senha_confirma'] ?? '';
        $dominio = substr(strrchr($email, "@"), 1);

        // Validações Base na Porta de Entrada (Front-gate)
        if (empty($nome) || empty($email) || empty($senha)) {
            $erro = "Todos os campos são de preenchimento obrigatório.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = "O formato do e-mail é inválido.";
        } elseif ($dominio === false || !checkdnsrr($dominio, 'MX')) {
            $erro = "O domínio do e-mail não é válido ou não recebe mensagens.";
        } elseif ($senha !== $senha_confirma) {
            $erro = "As palavras-passe não coincidem.";
        } elseif (strlen($senha) < 8) {
            $erro = "A palavra-passe deve ter pelo menos 8 caracteres.";
        } else {
            
            // DELEGAÇÃO PARA A COZINHA (CONTROLLER)
            $controller = new RegisterController();
            $resultado = $controller->registrarNovaEmpresa($nome, $email, $senha);

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
    <title>Kairós Connect - Criar Conta</title>
    <link rel="stylesheet" href="css/auth.css?v=1.0">
</head>
<body class="auth-body">
    <div class="auth-card">
        <h2>Kairós Connect</h2>
        <div class="auth-subtitle">Registe-se e ative o seu Workspace</div>

        <?php if($erro): ?>
            <div class="auth-alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="auth-form-group">
                <input type="text" name="nome" class="auth-control" placeholder="O seu Nome Completo" required value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>">
            </div>
            <div class="auth-form-group">
                <input type="email" name="email" class="auth-control" placeholder="O seu E-mail Profissional" required style="text-transform: lowercase;" value="<?php echo isset($_POST['email']) ? htmlspecialchars(strtolower($_POST['email'])) : ''; ?>">
            </div>
            <div class="auth-form-group">
                <input type="password" name="senha" class="auth-control" placeholder="Crie uma Palavra-passe" required minlength="8">
            </div>
            <div class="auth-form-group">
                <input type="password" name="senha_confirma" class="auth-control" placeholder="Confirme a Palavra-passe" required minlength="8">
            </div>
            <button type="submit" class="auth-btn-submit">Criar Conta</button>
        </form>

        <div class="auth-links-footer">
            Já tem uma conta? <a href="login.php">Iniciar Sessão aqui</a>
        </div>
    </div>
</body>
</html>