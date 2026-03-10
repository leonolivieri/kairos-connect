<?php
namespace src\Controllers;

use src\Models\Mensagem;
use src\Config\Database;
use src\Controllers\ConfigController;
use PDO;

/**
 * CLASSE: ChatController
 * PROJETO: Kairós Connect [cite: 2026-03-10]
 * OBJETIVO: Orquestrar Inteligência e Saída 100% Parametrizadas.
 */
class ChatController {
    private $db;
    private $mensagemModel;
    private $config;    

    public function __construct() {
        $this->db = Database::getInstance();
        $this->mensagemModel = new Mensagem();
        $this->config = new ConfigController();
    }

    public function processarMensagem($whatsappId, $numero, $nome, $texto, $logFile) {
        $this->mensagemModel->salvar($whatsappId, $numero, $nome, $texto);
        
        // 1. Coleta Sincronizada (Todas em MAIÚSCULAS) [cite: 2026-03-10]
        $configs = $this->getConfigs([
            'IA_API_KEY', 
            'IA_BASE_URL', 
            'IA_MODEL', 
            'IA_SYSTEM_PROMPT',
            'META_ACCESS_TOKEN', 
            'META_PHONE_ID', 
            'META_BASE_URL',
            'IS_IA_ACTIVE' 
        ]);

        // 2. Kill-Switch Operacional [cite: 2026-03-09, 2026-03-10]
        if (($configs['IS_IA_ACTIVE'] ?? '0') !== '1') {
             file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] IA DESATIVADA VIA BANCO." . PHP_EOL, FILE_APPEND);
             return "Serviço temporariamente indisponível.";
        }
        
        if (empty($configs['IA_API_KEY'])) {
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ERRO: Chave IA ausente no Banco!" . PHP_EOL, FILE_APPEND);
            return "Entendido, estou analisando.";
        }

        // 3. Parâmetros Dinâmicos (Montagem da URL) [cite: 2026-03-10]
        $urlGemini = "{$configs['IA_BASE_URL']}/{$configs['IA_MODEL']}:generateContent?key={$configs['IA_API_KEY']}";

        // 4. Geração de Resposta (Passando o System Prompt dinâmico) [cite: 2026-03-10]
        $respostaIA = $this->gerarRespostaIA($urlGemini, $nome, $texto, $logFile, $configs['IA_SYSTEM_PROMPT'] ?? null);

        // 5. Saída Meta (WhatsApp) [cite: 2026-03-10]
        if (!empty($configs['META_ACCESS_TOKEN']) && !empty($configs['META_PHONE_ID'])) {
            $urlMeta = "{$configs['META_BASE_URL']}/{$configs['META_PHONE_ID']}/messages";
            $metaResponse = $this->enviarParaWhatsapp($configs['META_ACCESS_TOKEN'], $urlMeta, $numero, $respostaIA);
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] RAW META RES: " . $metaResponse . PHP_EOL, FILE_APPEND);
        }

        return $respostaIA;
    }

    private function getConfigs($chaves) {
        $res = [];
        foreach ($chaves as $chave) {
            $res[$chave] = $this->config->get($chave);
        }
        return $res;
    }

    private function gerarRespostaIA($url, $nome, $texto, $logFile, $systemPrompt) {
        // Usa o prompt vindo do banco ou o fallback de segurança [cite: 2026-03-10]
        $prompt = $systemPrompt ?: "Aja como um assistente executivo da Kairós Ventures.";
        $payload = ["contents" => [["parts" => [["text" => $prompt . "\n\nCliente: " . $nome . "\nMensagem: " . $texto]]]]];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        $res = curl_exec($ch);
        curl_close($ch);

        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] RAW GEMINI RES: (Processado)" . PHP_EOL, FILE_APPEND);
        $result = json_decode($res, true);
        return $result['candidates'][0]['content']['parts'][0]['text'] ?? "Análise em andamento.";
    }

    private function enviarParaWhatsapp($token, $url, $para, $texto) {
        $payload = ["messaging_product" => "whatsapp", "to" => $para, "type" => "text", "text" => ["body" => $texto]];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
}