<?php
/**
 * ARQUIVO: admin_configs.php
 * LOCAL: Raiz do projeto (kairos-connect) [cite: 2026-03-10]
 * OBJETIVO: Gestão de Ativos com Login e Blindagem AES-256.
 */

require_once __DIR__ . '/bootstrap.php';
session_start();

use src\Models\ConfigRepository;

// 1. CONFIGURAÇÃO DE ACESSO (Busca no seu .env) [cite: 2026-03-10]
$admin_user = $_ENV['ADMIN_USER'] ?? 'leon'; 
$admin_pass = $_ENV['ADMIN_PASS'] ?? '123456'; // Altere no seu .env [cite: 2026-03-10]

// Lógica de Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_configs.php");
    exit;
}

// 2. PROCESSAMENTO DO LOGIN [cite: 2026-03-10]
if (isset($_POST['login'])) {
    if ($_POST['user'] === $admin_user && $_POST['pass'] === $admin_pass) {
        $_SESSION['autenticado'] = true;
    } else {
        $erro_login = "Usuário ou senha inválidos.";
    }
}

// BARREIRA DE SEGURANÇA: Se não estiver logado, exibe apenas o formulário de login
if (!isset($_SESSION['autenticado'])) {
?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8"><title>Login - Kairós</title>
        <style>
            body { font-family: sans-serif; background: #0f172a; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
            .card { background: #1e293b; padding: 30px; border-radius: 8px; width: 300px; text-align: center; color: white; }
            input { width: 100%; padding: 10px; margin: 10px 0; border-radius: 4px; border: 1px solid #334155; background: #0f172a; color: white; box-sizing: border-box; }
            button { width: 100%; padding: 10px; background: #38bdf8; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="card">
            <h2>Kairós Connect</h2>
            <?php if (isset($erro_login)) echo "<p style='color:red'>$erro_login</p>"; ?>
            <form method="POST">
                <input type="text" name="user" placeholder="Usuário" required>
                <input type="password" name="pass" placeholder="Senha" required>
                <button type="submit" name="login">Acessar Painel</button>
            </form>
        </div>
    </body>
    </html>
<?php
    exit;
}

// 3. ÁREA PROTEGIDA: LOGICA DE SALVAMENTO [cite: 2026-03-10]
$repo = new ConfigRepository();
$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    if ($repo->salvar($_POST)) { $mensagem = "✅ '{$_POST['chave']}' atualizado!"; }
}


$configs = $repo->listarTodos();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Gestão - Kairós</title>
    <style>
        body { font-family: sans-serif; background: #0f172a; color: white; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: #1e293b; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #334155; text-align: left; }
        textarea { width: 100%; background: #0f172a; color: #34d399; border: 1px solid #334155; padding: 5px; }
        .btn-save { background: #38bdf8; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between;">
            <h1>Configurações do Sistema</h1>
            <a href="?logout=1" style="color: #f87171;">Sair</a>
        </div>
        
        <?php if ($mensagem) echo "<p style='color:#34d399'>$mensagem</p>"; ?>

        <table>
            <thead><tr><th>Chave</th><th>Valor (Decifrado)</th><th>Ação</th></tr></thead>
            <tbody>
                <?php foreach ($configs as $c): 
                    $item = $repo->buscar($c['chave']); // Busca com decriptação [cite: 2026-03-10]
                ?>
                <tr>
                    <form method="POST">
                        <td>
                            <strong><?= $item['chave'] ?></strong><br>
                            <small><?= $item['config_group'] ?></small>
                            <input type="hidden" name="chave" value="<?= $item['chave'] ?>">
                            <input type="hidden" name="config_group" value="<?= $item['config_group'] ?>">
                        </td>
                        <td>
                            <textarea name="valor" rows="2"><?= htmlspecialchars($item['valor']) ?></textarea>
                            <input type="hidden" name="descricao" value="<?= $item['descricao'] ?>">
                        </td>
                        <td><button type="submit" name="salvar" class="btn-save">Salvar</button></td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>