<?php
    namespace src\Controllers;

    use src\Models\MessageRepository;
    use src\Config\Database;
    use src\Controllers\ConfigController;
    use PDO;

    /**
     * CLASSE: ConnecttController
     * PROJETO: Kairós Connect
     * STATUS: Versão 6.3 - Patch de Memória e Rastreio de Banco de Dados
     * DATA/HORA DE DEPLOY: 20 de Março de 2026
     */
    class ConnectController {
        private $db;
        private $mensagemModel;
        private $config;    

        public function __construct() {
            $this->db = Database::getInstance();
            $this->mensagemModel = new MessageRepository();
            $this->config = new ConfigController();
        }

        public function processarMensagem($whatsappId, $numero, $nome, $texto, $logFile) {
            
            // PASSO A: Tentar salvar a ENTRADA com auditoria de erro rígida
            try {
                $salvo = $this->mensagemModel->salvar($whatsappId, $numero, $nome, $texto, 'ENTRADA');
                if (!$salvo) {
                    $this->logInternal($logFile, "AVISO DE BANCO: Falha silenciosa ao salvar a ENTRADA. Verifique colunas e tipos.");
                }
            } catch (\Exception $e) {
                $this->logInternal($logFile, "ERRO SQL FATAL (ENTRADA): " . $e->getMessage());
            }
            
            $configs = $this->getConfigs([
                'IA_API_KEY', 'IA_BASE_URL', 'IA_MODEL', 'META_ACCESS_TOKEN', 
                'META_PHONE_ID', 'META_BASE_URL', 'IS_IA_ACTIVE' 
            ]);

            if (($configs['IS_IA_ACTIVE'] ?? '0') !== '1') {
                $this->logInternal($logFile, "IA DESATIVADA VIA BANCO.");
                return "Serviço temporariamente indisponível.";
            }
            
            if (empty($configs['IA_API_KEY'])) {
                $this->logInternal($logFile, "ERRO: IA_API_KEY não configurada.");
                return "Erro técnico: Configure a Chave de API no painel.";
            }

            $systemPrompt = $this->buscarPromptAtivo();
            $modeloAtivo = $configs['IA_MODEL'] ?: 'gemini-2.5-flash';
            
            $urlIA = "{$configs['IA_BASE_URL']}/{$modeloAtivo}:generateContent?key={$configs['IA_API_KEY']}";

            $this->logInternal($logFile, "ACORDANDO MOTOR: " . $modeloAtivo);

            $respostaIA = $this->gerarRespostaIA($urlIA, $nome, $texto, $logFile, $systemPrompt);

            // PASSO B: Salvar a SAÍDA (Resposta da IA) no banco de dados
            try {
                $idSaida = 'IA_OUT_' . uniqid(); // Gera um ID único provisório para a saída
                $salvoSaida = $this->mensagemModel->salvar($idSaida, $numero, 'Kairós IA', $respostaIA, 'SAIDA');
                if (!$salvoSaida) {
                    $this->logInternal($logFile, "AVISO DE BANCO: Falha silenciosa ao salvar a SAÍDA.");
                }
            } catch (\Exception $e) {
                $this->logInternal($logFile, "ERRO SQL FATAL (SAIDA): " . $e->getMessage());
            }

            if (!empty($configs['META_ACCESS_TOKEN']) && !empty($configs['META_PHONE_ID'])) {
                $urlMeta = "{$configs['META_BASE_URL']}/{$configs['META_PHONE_ID']}/messages";
                $metaResponse = $this->enviarParaWhatsapp($configs['META_ACCESS_TOKEN'], $urlMeta, $numero, $respostaIA);
                $this->logInternal($logFile, "RAW META RES: " . $metaResponse);
            }

            return $respostaIA;
        }

        private function getConfigs($chaves) {
            $res = [];
            foreach ($chaves as $chave) { $res[$chave] = $this->config->get($chave); }
            return $res;
        }

        private function buscarPromptAtivo() {
            try {
                $stmt = $this->db->query("SELECT valor FROM kairos_configuracoes WHERE config_group = 'IA_PROMPTS' AND is_active = 1 LIMIT 1");
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($res) return $res['valor'];
            } catch (\Exception $e) {}
            return $this->config->get('IA_SYSTEM_PROMPT') ?: "Aja como assistente executivo.";
        }

        private function gerarRespostaIA($url, $nome, $texto, $logFile, $systemPrompt) {
            // PAYLOAD UNIVERSAL: Resolve o Erro 400 fundindo as instruções no conteúdo.
            $conteudoCombinado = "DIRETRIZ DE SISTEMA (Siga estritamente esta persona):\n" . $systemPrompt . "\n\n---\n\nMENSAGEM DO CLIENTE:\nNome: " . $nome . "\nMensagem: " . $texto;

            $payload = [
                "contents" => [
                    [
                        "role" => "user",
                        "parts" => [
                            [
                                "text" => $conteudoCombinado
                            ]
                        ]
                    ]
                ]
            ];

            $ch = \curl_init($url);
            \curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            \curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            
            $res = \curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $this->logInternal($logFile, "HTTP CODE IA: " . $httpCode);
            
            $result = json_decode($res, true);
            $textoIA = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if ($textoIA) {
                return $textoIA;
            } else {
                $msgErro = $result['error']['message'] ?? "Erro interno da API Google.";
                $this->logInternal($logFile, "ERRO CAPTURADO: " . $msgErro);
                return "Leon, erro de validação: " . $msgErro;
            }
        }

        private function enviarParaWhatsapp($token, $url, $para, $texto) {
            $payload = ["messaging_product" => "whatsapp", "to" => $para, "type" => "text", "text" => ["body" => $texto]];
            $ch = curl_init($url);
            \curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]);
            \curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $res = curl_exec($ch);
            curl_close($ch);
            return $res;
        }

        private function logInternal($file, $msg) {
            file_put_contents($file, "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL, FILE_APPEND);
        }
    }