<?php
/**
 * =========================================================================
 * PROJETO: Kairós Connect
 * ARQUIVO: src/Controllers/OmniController.php
 * OBJETIVO: Controlador da interface Omnichannel (Fornecedor de Dados/API)
 * VERSÃO: 1.0.0
 * DATA/HORA: 18/03/2026 - 17:49
 * IMPLEMENTAÇÃO: Estruturação dos métodos listarConversas e listarHistorico 
 * para consumo via fetch() no frontend.
 * =========================================================================
 */

namespace src\Controllers;

use src\Models\MessageRepository;
use Exception;

class OmniController {
    
    private $messageRepo;

    public function __construct() {
        $this->messageRepo = new MessageRepository();
    }

    /**
     * Retorna a lista de contatos ativos em formato JSON.
     * Estrutura compatível com o public/js/omni.js
    */
    public function listarConversas() {
        try {
            $contatos = $this->messageRepo->getContatosAtivos();
            
$dadosFormatados = [];
            foreach ($contatos as $c) {
                $telefone = $c['telefone_cliente'];
                
                // Busca o histórico
                $historico = $this->messageRepo->getHistorico($telefone);
                $ultimaMsg = end($historico); 
                
                // EXTRAÇÃO CIRÚRGICA DO NOME: Se existir nome no banco, usa ele. Se não, usa o telefone.
                $nomeReal = $ultimaMsg['remetente_nome'] ?? $telefone;
                
                $dadosFormatados[] = [
                    'id' => $telefone,
                    'nome' => $nomeReal, 
                    'telefone' => $telefone,
                    'ultima_mensagem' => $ultimaMsg ? $ultimaMsg['mensagem'] : '...',
                    'status' => 'Ativo',
                    'mensagens' => $this->formatarMensagens($historico)
                ];
            }

            return json_encode([
                '_metadata' => ['status' => 'sucesso'],
                'dados' => $dadosFormatados
            ]);

        } catch (Exception $e) {
            return json_encode([
                '_metadata' => ['status' => 'erro', 'mensagem' => $e->getMessage()],
                'dados' => []
            ]);
        }
    }

    /**
     * Formata o histórico do banco para o padrão de balões do frontend
     * PATCH 1.1: Correção de Fuso Horário (BRT) e Mapeamento de Direção
    */
    private function formatarMensagens($historicoBruto) {
        $mensagens = [];
        
        // Define o fuso horário do Banco (UTC) e o fuso real da Operação (São Paulo)
        $fusoBanco = new \DateTimeZone('UTC');
        $fusoLocal = new \DateTimeZone('America/Sao_Paulo');

        foreach ($historicoBruto as $msg) {
            
            // 1. TRADUÇÃO DE DIREÇÃO (O Espelhamento Visual)
            $tipoBalao = 'cliente'; // Padrão
            $direcao = strtoupper($msg['direcao'] ?? '');
            
            // Aceita tanto a nova padronização (SAIDA) quanto registros antigos (OUT)
            if ($direcao === 'SAIDA' || $direcao === 'OUT') {
                $tipoBalao = 'ia'; 
            }

            // 2. TRADUÇÃO TEMPORAL (Correção do Fuso Horário)
            $horaFormatada = '';
            $dataCrua = $msg['data_envio'] ?? ($msg['created_at'] ?? null);
            
            if ($dataCrua) {
                try {
                    $dateObj = new \DateTime($dataCrua, $fusoBanco);
                    $dateObj->setTimezone($fusoLocal);
                    $horaFormatada = $dateObj->format('H:i');
                } catch (\Exception $e) {
                    $horaFormatada = '00:00'; // Fallback de segurança
                }
            }

            // 3. MONTAGEM DO PACOTE JSON
            $mensagens[] = [
                'tipo'  => $tipoBalao,
                'texto' => $msg['mensagem'] ?? ($msg['mensagem_texto'] ?? ''),
                'hora'  => $horaFormatada
            ];
        }

        return $mensagens;
    }
}