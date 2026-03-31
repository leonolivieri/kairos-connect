<?php
    /**
     * =========================================================================
     * PROJETO: Kairós Connect
     * ARQUIVO: public/api_enviar_msg.php
     * OBJETIVO: Roteador para recebimento de mensagens do Painel Omnichannel
     * VERSÃO: 1.0.0 (Transbordo Humano)
     * CRIADO EM: 21/03/2026 - 10:44
     * IMPLEMENTAÇÃO: Recebe o POST do omni.js e aciona o OmniController para
     * disparar a mensagem via Meta e registrar a "Sessão Fantasma".
     * =========================================================================
    */
    use src\Controllers\OmniController;

    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';
    $logFile = __DIR__ . '/webhook_debug.log';

    header('Content-Type: application/json');

    // Proteção contra requisições indevidas
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['_metadata' => ['status' => 'erro', 'mensagem' => 'Método inválido. Use POST.']]);
        exit;
    }

    // Captura do Payload enviado pelo JavaScript
    $payload = json_decode(file_get_contents('php://input'), true);
    $telefone = $payload['telefone'] ?? null;
    $texto = $payload['texto'] ?? null;

    if (!$telefone || !$texto) {
        echo json_encode(['_metadata' => ['status' => 'erro', 'mensagem' => 'Telefone ou texto ausente.']]);
        exit;
    }
    file_put_contents($logFile, "Chegou bem até aqui: Telefone: $telefone | Texto: $texto" . PHP_EOL, FILE_APPEND);
    // Ignição do Controlador
    $controller = new OmniController();
    echo $controller->enviarMensagemHumana($telefone, $texto);