<?php
    namespace src\Services;

    use Exception;
    use src\Controllers\ConfigController;

    /**
     * CLASSE: WhatsAppService
     * OBJETIVO: Centralizar toda a comunicação de saída com a Meta API.
    */
    class WhatsAppService {

        private $token;
        private $baseUrl;
        private $phoneId;

        public function __construct() {
            $config = new ConfigController();
            $this->token    = $config->get('META_ACCESS_TOKEN');
            $this->baseUrl  = $config->get('META_BASE_URL');
            $this->phoneId  = $config->get('META_PHONE_ID');

            if (empty($this->token) || empty($this->baseUrl) || empty($this->phoneId)) {
                throw new Exception("ERRO DE CONFIGURAÇÃO: Verifique as chaves META no banco de dados.");
            }
        }
        public function enviarTexto($telefoneDestino, $texto) {
            $urlFinal = rtrim($this->baseUrl, '/') . '/' . $this->phoneId . '/messages';
            $payload = [
                "messaging_product" => "whatsapp", 
                "to"                => $telefoneDestino, 
                "type"              => "text", 
                "text"              => ["body" => $texto]
            ];
            
            $ch = curl_init($urlFinal);
            $authHeader = 'Authorization: Bearer ' . str_replace('Bearer ', '', $this->token);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [$authHeader, "Content-Type: application/json"]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $respostaMeta = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode !== 200) {
                throw new Exception("Erro Meta API: " . $respostaMeta);
            }

            return $respostaMeta;
        }
    }