<?php
/**
 * ARQUIVO: public/webhook.php
 * OBJETIVO: Endpoint de recepção para a API do WhatsApp (Meta)
 */

require_once __DIR__ . '/../../kairos-connect/bootstrap.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

use src\Controllers\ConfigController;

$config = new ConfigController();

echo "=== INICIALIZANDO WEBHOOK DE PAGAMENTO ===<br>";
// 1. LÓGICA DE VERIFICAÇÃO (MÉTODO GET)
// Exigido pela Meta para validar o Webhook no painel Developers
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $verifyToken = $config->get('meta_verify_token'); // Recupera o token que salvamos (KAIROS_LAB_2026)
    
    $mode      = $_GET['hub_mode'] ?? '';
    $token     = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';

    if ($mode === 'subscribe' && $token === $verifyToken) {
        echo $challenge;
        http_response_code(200);
        exit;
    } else {
        http_response_code(403);
        exit;
    }
}

// 2. LÓGICA DE RECEPÇÃO DE MENSAGENS (MÉTODO POST)
// Onde as mensagens reais chegarão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // LOG TEMPORÁRIO PARA MINERAÇÃO DE ATIVOS (Passo 2.2 da Tarefa)
    // Isso criará um arquivo .log para analisarmos o JSON da Meta
    file_put_contents(__DIR__ . '/webhook_debug.log', "[" . date('Y-m-d H:i:s') . "] PAYLOAD: " . $input . PHP_EOL, FILE_APPEND);

    // Por enquanto, respondemos apenas 200 OK para a Meta não reenviar a mesma mensagem
    http_response_code(200);
    exit;
}