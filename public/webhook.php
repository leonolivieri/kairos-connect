<?php
/**
 * ARQUIVO: public/webhook.php
 * OBJETIVO: Endpoint Consolidado (Auditoria + Segurança + Inteligência + Resposta Real)
 */

require_once __DIR__ . '/../../kairos-connect/bootstrap.php';

// Silenciamos erros na tela (exigência da Meta), mas capturamos internamente [cite: 2026-03-08]
ini_set('display_errors', 0);
error_reporting(E_ALL);

use src\Controllers\ConfigController;
use src\Controllers\ChatController;

$config = new ConfigController();
$logFile = __DIR__ . '/webhook_debug.log';

// 1. VALIDAÇÃO (GET) - Sua lógica original preservada integralmente [cite: 2026-03-08]
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $verifyToken = $config->get('meta_verify_token'); 
    
    $mode      = $_GET['hub_mode'] ?? '';
    $token     = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';

    if ($mode === 'subscribe' && $token === $verifyToken) {
        header('Content-Type: text/plain');
        echo $challenge;
        exit;
    } else {
        http_response_code(403);
        exit;
    }
}

// 2. RECEPÇÃO (POST) - Com Blindagem Estratégica [cite: 2026-03-09]
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    
    // Seu log de segurança (Caixa Preta)
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] NOVO POST: " . $input . PHP_EOL, FILE_APPEND);

    $data = json_decode($input); 

    if (isset($data->entry[0]->changes[0]->value->messages[0])) {
        
        try {
            $msgData = $data->entry[0]->changes[0]->value->messages[0];
            $contato = $data->entry[0]->changes[0]->value->contacts[0] ?? null;

            // Extração limpa (Suas variáveis) [cite: 2026-03-08]
            $whatsappId = $msgData->id;
            $numero     = $msgData->from;
            $nome       = $contato->profile->name ?? 'Cliente';
            $texto      = $msgData->text->body ?? '';

            // O MAESTRO: Chama o Controller (Salva, Pensa e Agora ENVIARÁ para a Meta) [cite: 2026-03-09]
            $chat = new ChatController();
            // Passamos o $logFile como quinto parâmetro para o Controller não se perder
            $respostaIA = $chat->processarMensagem($whatsappId, $numero, $nome, $texto, $logFile);

            // REGISTRO DE SUCESSO NO SEU LOG VISÍVEL (Substituindo o error_log tímido) [cite: 2026-03-08]
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] RESPOSTA GERADA: " . $respostaIA . PHP_EOL, FILE_APPEND);

        } catch (\Exception $e) {
            // Em caso de falha na IA ou na API, registramos o motivo exato aqui [cite: 2026-03-09]
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ERRO NO PROCESSAMENTO: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }

    http_response_code(200);
    echo "OK";
    exit;
}