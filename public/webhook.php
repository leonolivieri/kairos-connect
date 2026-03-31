<?php
    /**
     * ARQUIVO: public/webhook.php
     * OBJETIVO: Endpoint Consolidado (Auditoria + Segurança + Inteligência + Resposta Real)
    */

    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';

    use src\Controllers\ConfigController;
    use src\Controllers\ConnectController;

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

    // 2. RECEPÇÃO (POST) - Com Blindagem Estratégica
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // ATO 1: O PORTEIRO CEGO (Libera a Meta Instantaneamente)
        // ---------------------------------------------------------------------
        ob_start();
        http_response_code(200);
        echo "OK";
        header("Connection: close");
        header("Content-Length: " . ob_get_length());
        ob_end_flush();
        @ob_flush();
        flush();
        
        // Corta o "fio do telefone" com a Meta, mas mantém o PHP vivo na RAM
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        $input = file_get_contents('php://input');
        
        // Seu log de segurança (Caixa Preta)
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] NOVO POST: " . $input . PHP_EOL, FILE_APPEND);

        $data = json_decode($input); 

        // Verifica se é uma mensagem e se O TIPO é 'text'
        if (isset($data->entry[0]->changes[0]->value->messages[0])) {
            $msgData = $data->entry[0]->changes[0]->value->messages[0];
            
            // FILTRO DE BLINDAGEM: Ignora áudio, imagem, reação, etc.
            if (($msgData->type ?? '') === 'text') {
                try {
                    $contato = $data->entry[0]->changes[0]->value->contacts[0] ?? null;

                    // Extração limpa
                    $whatsappId = $msgData->id;
                    $numero     = $msgData->from;
                    $nome       = $contato->profile->name ?? 'Cliente';
                    $texto      = $msgData->text->body;

                    // O MAESTRO: Chama o Controller
                    $chat = new ConnectController();
                    $respostaIA = $chat->processarMensagem($whatsappId, $numero, $nome, $texto, $logFile);

                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] RESPOSTA GERADA: " . $respostaIA . PHP_EOL, FILE_APPEND);

                } catch (\Exception $e) {
                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ERRO NO PROCESSAMENTO: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
                }
            } else {
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] AVISO: Mensagem ignorada (Não é texto). Tipo: " . ($msgData->type ?? 'desconhecido') . PHP_EOL, FILE_APPEND);
            }
        }

        exit;
    }