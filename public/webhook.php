<?php
/**
 * ARQUIVO: public/webhook.php
 * PADRÃO: Engenharia Kairós - Isolamento de Ativos
 */

// O arquivo está em public_html/connect/
// 1º pulo (../) vai para public_html/
// 2º pulo (../../) vai para a RAIZ da conta, onde está a pasta kairos-connect/

require_once __DIR__ . '/../../kairos-connect/src/Config/Database.php';
require_once __DIR__ . '/../../kairos-connect/src/Controllers/ConfigController.php';

use App\Controllers\ConfigController;

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