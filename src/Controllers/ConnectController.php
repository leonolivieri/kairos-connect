<?php
namespace src\Controllers;

use src\Models\MessageRepository;
use src\Controllers\ConfigController;
use src\Services\WhatsAppService;
use src\Services\IAService;
use Exception;

/**
 * =========================================================================
 * CLASSE: ConnectController
 * PROJETO: Kairós Connect
 * STATUS: Versão 7.1 - Arquitetura de Serviços Autônomos e Transbordo Híbrido
 * DATA/HORA DE ALTERAÇÃO: 30/03/2026 - 13:45
 * IMPLEMENTAÇÃO:
 * - Regra Global (IS_IA_ACTIVE)
 * - Máquina de Estados (kairos_sessoes)
 * - Retomada por Wake Word e Retomada por Tempo (24h)
 * =========================================================================
 */
class ConnectController {
    private $mensagemModel;
    private $config;    

    public function __construct() {
        $this->mensagemModel = new MessageRepository();
        $this->config = new ConfigController();
    }

    public function processarMensagem($whatsappId, $numero, $nome, $texto, $logFile) {
        
        // PASSO A: Registro de Entrada
        try {
            $this->mensagemModel->salvar($whatsappId, $numero, $nome, $texto, 'ENTRADA');

            // 2. Libera a Meta: Desliga a conexão HTTP mas mantém o script rodando
            http_response_code(200);
            echo "OK";
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            
        } catch (Exception $e) {
            $this->logInternal($logFile, "ERRO BANCO (ENTRADA): " . $e->getMessage());
        }
        // =========================================================================
        // MÓDULO DE TRANSBORDO E REGRAS DE ESTADO (HANDOFF)
        // =========================================================================
        
        // REGRA GLOBAL: O motor cognitivo geral da empresa está ligado?
        $iaGlobalStatus = $this->config->get('IS_IA_ACTIVE');
        // Se a chave for '0', 'false', ou nula, abortamos o processamento da IA.
        if ($iaGlobalStatus == '0' || $iaGlobalStatus === 'false' || empty($iaGlobalStatus)) {
            $this->logInternal($logFile, "REGRA GLOBAL: IA Desativada. Mensagem arquivada para atendimento humano.");
            return "IA Global Desativada.";
        }

        // REGRA DE SESSÃO: Qual o status deste cliente específico?
        $sessao = $this->mensagemModel->getEstadoSessao($numero);
        $iaRespondeSessao = $sessao['ia_responde'] ?? 1;
        $dataIntervencao = $sessao['data_intervencao'] ?? null;

        // Se a IA estiver desligada para este cliente (0)
        if ($iaRespondeSessao == 0) {
            $reativarIA = false;
            $motivoReativacao = "";

            // 1. Verificação de Wake Word (Palavra de Despertar)
            $textoLower = mb_strtolower($texto, 'UTF-8');
            // Expressões que o cliente pode dizer para pedir a IA de volta
            if (preg_match('/\b(falar com a ia|voltar para a ia|assistente virtual|olá kairós|kairos)\b/', $textoLower)) {
                $reativarIA = true;
                $motivoReativacao = "Wake Word do Cliente";
            }
            
            // Comando oculto para o Operador Humano (Leon) religar a IA rapidamente
            if (trim($texto) === '/ia_on' || trim($texto) === '#ia') {
                 $reativarIA = true;
                 $motivoReativacao = "Comando do Operador";
            }

            // 2. Verificação de Tempo (Regra das 24 horas)
            if (!$reativarIA && $dataIntervencao) {
                try {
                    $agora = new DateTime();
                    $dataInterv = new DateTime($dataIntervencao);
                    $diferencaHoras = ($agora->getTimestamp() - $dataInterv->getTimestamp()) / 3600;

                    if ($diferencaHoras >= 24) {
                        $reativarIA = true;
                        $motivoReativacao = "Tempo Expirado (24h)";
                    }
                } catch (Exception $e) {
                    $this->logInternal($logFile, "Erro ao calcular tempo: " . $e->getMessage());
                }
            }

            // Ação de Reativação ou Silêncio
            if ($reativarIA) {
                // Liga a IA no banco de dados e apaga a data de intervenção
                $this->mensagemModel->setEstadoSessao($numero, 1);
                $this->logInternal($logFile, "TRANSBORDO: IA reativada. Motivo: " . $motivoReativacao);
                
                // Se foi apenas um comando do operador, não mandamos o "/ia_on" pro Gemini. Abortamos aqui.
                if ($motivoReativacao === "Comando do Operador") {
                    return "IA religada silenciosamente.";
                }
            } else {
                // A IA continua calada. A mensagem foi salva no PASSO A. O Maestro encerra o ciclo.
                $this->logInternal($logFile, "TRANSBORDO: Cliente em atendimento humano. IA em silêncio.");
                return "Em atendimento humano.";
            }
        }
        // =========================================================================

        // PASSO B: Processamento Cognitivo (Motor de IA)
        try {
            $iaService = new IAService();
            $prompt = $this->config->get('IA_SYSTEM_PROMPT') ?? "Atue como um assistente virtual prestativo e profissional.";
            
            // O Maestro apenas pede a música, o IAService é quem toca.
            $respostaIA = $iaService->gerarResposta($nome, $texto, $prompt);
            
            $this->logInternal($logFile, "IA RESPONDEU: " . mb_substr($respostaIA, 0, 50) . "...");
        } catch (Exception $e) {
            $this->logInternal($logFile, "FALHA MOTOR COGNITIVO: " . $e->getMessage());
            return "Desculpe, tive um problema técnico ao processar sua solicitação.";
        }

        // PASSO C: Despacho (WhatsApp Service)
        try {
            // Salvamos a SAÍDA no banco antes do disparo
            $whatsappIdSaida = 'saida_' . uniqid();
            $this->mensagemModel->salvar($whatsappIdSaida, $numero, 'IA', $respostaIA, 'SAIDA');

            $this->enviarParaWhatsapp($numero, $respostaIA);
            $this->logInternal($logFile, "DISPARO WHATSAPP REALIZADO.");
        } catch (Exception $e) {
            $this->logInternal($logFile, "FALHA NO DISPARO: " . $e->getMessage());
        }

        return $respostaIA;
    }

    /**
     * Encaminha o texto para o serviço de mensageria
     */
    private function enviarParaWhatsapp($para, $texto) {
        $whatsappService = new WhatsAppService();
        return $whatsappService->enviarTexto($para, $texto);
    }

    private function logInternal($logFile, $msg) {
        $data = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$data}] {$msg}\n", FILE_APPEND);
    }
}