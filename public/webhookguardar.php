<?php
/**
 * ARQUIVO: public/webhook.php
 * PADRÃO: Engenharia Kairós - Isolamento de Ativos
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
// 1. Descobrimos se estamos na Hostinger ou no Localhost
$isHostinger = (strpos(__DIR__, 'public_html') !== false);
echo "=== INICIALIZANDO WEBHOOK DE PAGAMENTO ===<br>";
echo "Ambiente Detectado: " . ($isHostinger ? "Hostinger (Produção)" : "Localhost (Desenvolvimento)") . "<br><br>";
// O arquivo está em public_html/connect/
// 1º pulo (../) vai para public_html/
// 2º pulo (../../) vai para a RAIZ da conta, onde está a pasta kairos-connect/

if ($isHostinger) {
    // Usamos o caminho real que o servidor nos confessou nos logs
    $basePath = '/home/u818458777/kairos-connect';
    
    echo "=== VALIDAÇÃO DE ACESSO DIRETO ===<br>";
    
    $pathDb = $basePath . '/src/config/database.php';
    $pathCtrl = $basePath . '/src/controllers/ConfigController.php';

    if (file_exists($pathDb)) {
        echo "✅ Database.php: ACESSO OK<br>";
        require_once $pathDb;
    } else {
        echo "❌ Database.php: NÃO ACESSÍVEL em $pathDb<br>";
    }

    if (file_exists($pathCtrl)) {
        echo "✅ ConfigController.php: ACESSO OK<br>";
        require_once $pathCtrl;
    } else {
        echo "❌ ConfigController.php: NÃO ACESSÍVEL em $pathCtrl<br>";
    }
}
use App\Controllers\ConfigController;

// ... seus echos e requires ...

try {
    echo "Tentando conectar ao banco...<br>";
    $db = \App\Config\Database::getInstance();
    echo "✅ Conexão estabelecida com sucesso!<br>";
} catch (\Exception $e) {
    echo "❌ Falha na conexão: " . $e->getMessage() . "<br>";
}

$config = new ConfigController();

// Lógica de Verificação (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $verifyToken = $config->get('meta_verify_token');
    
    if (($_GET['hub_mode'] ?? '') === 'subscribe' && ($_GET['hub_verify_token'] ?? '') === $verifyToken) {
        echo $_GET['hub_challenge'];
        http_response_code(200);
        exit;
    }
    http_response_code(403);
    exit;
}

// Lógica de Recepção (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    // Log de segurança para análise (fica dentro da public para debug inicial)
    file_put_contents(__DIR__ . '/webhook_debug.log', "[" . date('Y-m-d H:i:s') . "] PAYLOAD: " . $input . PHP_EOL, FILE_APPEND);

    http_response_code(200);
    exit;
}