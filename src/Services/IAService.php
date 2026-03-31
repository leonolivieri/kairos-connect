<?php
namespace src\Services;

use Exception;
use src\Controllers\ConfigController;

/**
 * CLASSE: IAService
 * OBJETIVO: Interface de processamento cognitivo da Kairós Ventures.
 * PROTEÇÃO: Sem referências estáticas a provedores externos (White Label).
 */
class IAService {
    private $chaveAcesso;
    private $urlBase;
    private $modeloAtivo;

    public function __construct() {
        $config = new ConfigController();
        
        // Busca chaves genéricas no banco de dados
        $this->chaveAcesso  = $config->get('IA_API_KEY');
        $this->urlBase      = $config->get('IA_BASE_URL');
        $this->modeloAtivo  = $config->get('IA_MODEL');

        if (empty($this->chaveAcesso) || empty($this->urlBase)) {
            throw new Exception("Falha Crítica: Parâmetros do Motor de IA não configurados.");
        }
    }

    /**
     * Gera resposta baseada em processamento neural externo.
     */
    public function gerarResposta($nome, $pergunta, $instrucoesSistema) {
        // A URL é montada dinamicamente pelo que vem do banco
        $urlProcessamento = "{$this->urlBase}/{$this->modeloAtivo}:generateContent?key={$this->chaveAcesso}";
        
        $corpoRequisicao = [
            "contents" => [
                [
                    "role" => "user", 
                    "parts" => [
                        ["text" => "Diretriz: {$instrucoesSistema}\n\nInterlocutor {$nome} solicita: {$pergunta}"]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.7
            ]
        ];

        $ch = curl_init($urlProcessamento);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($corpoRequisicao));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $execucao = curl_exec($ch);
        $codigoResposta = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($codigoResposta !== 200) {
            $erroDetectado = json_decode($execucao, true);
            $mensagemInterna = $erroDetectado['error']['message'] ?? "Indisponibilidade no processamento.";
            throw new Exception("Falha no Motor Cognitivo (Status $codigoResposta): " . $mensagemInterna);
        }

        $dados = json_decode($execucao, true);
        return $dados['candidates'][0]['content']['parts'][0]['text'] ?? "O motor não gerou uma saída válida.";
    }
}