<?php
    /**
     * =========================================================================
     * PROJETO: Kairós Connect
     * ARQUIVO: src/Models/MessageRepository.php
     * OBJETIVO: Gestão de leitura e escrita na tabela kairos_mensagens e sessoes
     * VERSÃO: 2.1.0 
     * DATA/HORA DE CRIAÇÃO: 18/03/2026 - 17:46
     * DATA/HORA DE ALTERAÇÃO: 30/03/2026 - 13:30
     * RESPONSÁVEL: Leon (Arquiteto Kairós Ventures)
     * IMPLEMENTAÇÃO: Inclusão da Máquina de Estados (Tabela kairos_sessoes) 
     * para gerir o Transbordo (IA vs Humano). Métodos getEstadoSessao e setEstadoSessao.
     * =========================================================================
    */

    namespace src\Models;

    use src\Config\Database;
    use PDO;
    use Exception;

    class MessageRepository {
        
        private $db;

        public function __construct() {
            // Conecta à base de dados usando a Fundação Kairós (Singleton)
            $this->db = Database::getInstance();
        }

        /**
         * MÉTODOS DE ESCRITA (Ação)
         * Salva uma nova mensagem no banco de dados.
         */
        public function salvar($whatsappId, $numero, $nome, $texto, $direcao) {
            try {
                // Força o Enum para maiúsculo para evitar erro no banco
                $direcaoEnum = strtoupper($direcao); 

                $query = "INSERT INTO kairos_mensagens 
                        (whatsapp_id, remetente_numero, remetente_nome, mensagem_texto, direcao, status_leitura) 
                        VALUES (:wId, :num, :nome, :texto, :dir, 0)";
                
                $stmt = $this->db->prepare($query);
                
                return $stmt->execute([
                    ':wId'   => $whatsappId,
                    ':num'   => $numero,
                    ':nome'  => $nome,
                    ':texto' => $texto,
                    ':dir'   => $direcaoEnum
                ]);
            } catch (Exception $e) {
                error_log("Erro ao salvar mensagem: " . $e->getMessage());
                return false;
            }
        }

        /**
         * Retorna a lista de contatos únicos que já interagiram com o bot.
         * KPI: Base do painel lateral esquerdo do Omnichannel.
         */
        public function getContatosAtivos() {
            try {
                // Usamos DISTINCT para não repetir o mesmo número 100 vezes na lista
                $query = "SELECT DISTINCT remetente_numero AS telefone_cliente, remetente_nome 
                          FROM kairos_mensagens 
                          WHERE direcao = 'ENTRADA' 
                          ORDER BY created_at DESC";
                
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                error_log("Erro ao buscar contatos ativos: " . $e->getMessage());
                return [];
            }
        }

        /**
         * Retorna todo o histórico de um número específico, em ordem cronológica.
         * KPI: Fidelidade na reconstrução do diálogo no Omnichannel.
         */
        public function getHistorico($telefone) {
            try {
                // Mapeamento exato via ALIAS (AS) para não quebrar a interface visual
                $query = "SELECT id, 
                                whatsapp_id, 
                                remetente_numero AS telefone_cliente, 
                                remetente_nome, 
                                mensagem_texto AS mensagem, 
                                direcao, 
                                status_leitura,
                                created_at AS data_envio 
                        FROM kairos_mensagens 
                        WHERE remetente_numero = :telefone 
                        ORDER BY created_at ASC";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':telefone', $telefone);
                $stmt->execute();
                
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                error_log("Erro ao buscar histórico de {$telefone}: " . $e->getMessage());
                return [];
            }
        }

        /**
         * =========================================================================
         * MÁQUINA DE ESTADOS (MÓDULO DE TRANSBORDO)
         * =========================================================================
         */

        /**
         * Lê o estado atual da IA para um telefone específico.
         * Se o cliente não existir na tabela de sessões, cria com a IA ligada (1).
         */
        public function getEstadoSessao($telefone) {
            try {
                $query = "SELECT ia_responde, data_intervencao FROM kairos_sessoes WHERE telefone_cliente = :telefone LIMIT 1";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':telefone' => $telefone]);
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($resultado) {
                    return $resultado; // Retorna o array com 'ia_responde' e 'data_intervencao'
                }

                // Se não existe, insere o cliente novo com a IA ligada por padrão
                $insertQuery = "INSERT INTO kairos_sessoes (telefone_cliente, ia_responde) VALUES (:telefone, 1)";
                $insertStmt = $this->db->prepare($insertQuery);
                $insertStmt->execute([':telefone' => $telefone]);

                return ['ia_responde' => 1, 'data_intervencao' => null];

            } catch (Exception $e) {
                error_log("Erro ao buscar estado da sessao para {$telefone}: " . $e->getMessage());
                // Em caso de falha no banco, por segurança, deixamos a IA responder.
                return ['ia_responde' => 1, 'data_intervencao' => null]; 
            }
        }

        /**
         * Altera o estado da IA (Ligar/Desligar).
         * @param string $telefone
         * @param int $iaResponde (1 para ON, 0 para OFF)
         */
        public function setEstadoSessao($telefone, $iaResponde) {
            try {
                // Se estamos desligando a IA (0), marcamos a hora. Se ligando (1), limpamos a hora (NULL).
                $dataInsercao = ($iaResponde == 0) ? date('Y-m-d H:i:s') : null;

                $query = "INSERT INTO kairos_sessoes (telefone_cliente, ia_responde, data_intervencao) 
                          VALUES (:telefone, :iaResponde, :dataIntervencao)
                          ON DUPLICATE KEY UPDATE 
                          ia_responde = VALUES(ia_responde),
                          data_intervencao = VALUES(data_intervencao)";
                
                $stmt = $this->db->prepare($query);
                return $stmt->execute([
                    ':telefone' => $telefone,
                    ':iaResponde' => $iaResponde,
                    ':dataIntervencao' => $dataInsercao
                ]);

            } catch (Exception $e) {
                error_log("Erro ao atualizar estado da sessao para {$telefone}: " . $e->getMessage());
                return false;
            }
        }
    }