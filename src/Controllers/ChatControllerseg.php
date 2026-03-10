<?php
namespace src\Controllers;

use src\Models\Mensagem;
use src\Config\Database;
use PDO;

/**
 * CLASSE: ChatController
 * OBJETIVO: Orquestrar a inteligência artificial (Gemini) e a persistência.
 */
class ChatController {
    private $db;
    private $mensagemModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->mensagemModel = new Mensagem();
    }

    public function processarMensagem($whatsappId, $numero, $nome, $texto) {
        // 1. Salva a entrada do cliente (Auditória) [cite: 2026-03-08]
        $this->mensagemModel->salvar($whatsappId, $numero, $nome, $texto);

        // 2. Busca a chave do Gemini que você inseriu no banco
        $stmt = $this->db->prepare("SELECT valor FROM kairos_configuracoes WHERE chave = 'GEMINI_API_KEY' AND is_active = 1");
        $stmt->execute();
        $apiKey = $stmt->fetchColumn();

        if (!$apiKey) {
            return "Erro: Chave API do Gemini não encontrada ou inativa.";
        }

        // 3. Define a "Personalidade" Executiva da Kairós [cite: 2026-03-08]
        $systemPrompt = "Você é o Arquiteto Executivo da Kairós Ventures. Seu tom de voz é profissional, direto e executivo. Ajude o cliente de forma didática.";

        // 4. Conecta com o Cérebro (Gemini API) via cURL
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;
        
        $payload = [
            "contents" => [
                ["parts" => [
                    ["text" => $systemPrompt . "\n\nCliente: " . $nome . "\nMensagem: " . $texto]
                ]]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        
        // Retorna a resposta real da IA ou um fallback seguro
        return $result['candidates'][0]['content']['parts'][0]['text'] ?? "Entendido, Leon. Recebi sua mensagem e estou analisando.";
    }
}