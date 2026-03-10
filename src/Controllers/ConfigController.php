<?php

    /** 
     * CLASSE: ConfigController
     * PROJETO: Kairós Connect [cite: 2026-03-09]
     * OBJETIVO: Gestão de Parâmetros do Sistema
    */

    namespace src\Controllers;

    use src\Config\Database;
    use src\Helpers\SecurityHelper;
    use PDO;

    /**
    * Classe ConfigController - Gestão de Parâmetros do Sistema
    */

    class ConfigController {
        private $db;

        public function __construct() {
            $this->db = Database::getInstance();
        }

        /*
        * Obtém uma configuração decifrada (se necessário)
        * @param string $chave Chave da configuração
        * @return string Valor processado
        */

        public function get($chave) {
            // 1. Normalização (Garante que a busca seja sempre em Maiúsculas)
            
            $chave = strtoupper(trim($chave));

            $stmt = $this->db->prepare("SELECT valor, config_group FROM kairos_configuracoes WHERE chave = :chave LIMIT 1");
            $stmt->execute([':chave' => $chave]);
            $result = $stmt->fetch();

            if (!$result) return null;

            // 2. Lógica de Decifragem (Baseada nas chaves sensíveis que definimos)
            // Se a chave buscada for uma das protegidas, tentamos decifrar o valor.
            if (in_array($chave, ['META_ACCESS_TOKEN', 'META_VERIFY_TOKEN', 'IA_API_KEY', 'MASTER_KEY'])) {
                return SecurityHelper::decrypt($result['valor']) ?: $result['valor'];
            }

            return $result['valor'];
        }

        /**
         * Salva ou atualiza uma configuração com blindagem e preenchimento de TODOS os campos
         * @param string $chave Chave da configuração
         * @param string $valor Conteúdo
         * @param string $descricao Texto explicativo para o banco
         * @param string $categoria Categoria (Analise de Mercado, Configuração, Sistema)
         * @param string $grupo Grupo de agrupamento (Ex: WHATSAPP_API)
         * @param int $ativo Status de ativação (0 ou 1)
         */

        public function set($chave, $valor, $descricao = '', $categoria = 'Sistema', $grupo = 'Sistema', $ativo = 1) {
                // ALTERADO: Adicionada a proteção para o grupo IA_CONFIG [cite: 2026-03-09]
                $chave = strtoupper(trim($chave));

                // 2. Trava de Segurança por Chave [cite: 2026-03-10]
                if (in_array($chave, ['META_ACCESS_TOKEN', 'META_VERIFY_TOKEN', 'IA_API_KEY', 'MASTER_KEY'])) {
                    $valor = SecurityHelper::encrypt($valor);
                }

                // Mudança sutil: trocamos ":valor" por "VALUES(valor)" no UPDATE
                $sql = "INSERT INTO kairos_configuracoes (chave, valor, descricao, categoria, config_group, is_active) 
                        VALUES (:chave, :valor, :descricao, :categoria, :grupo, :ativo) 
                        ON DUPLICATE KEY UPDATE 
                            valor           = VALUES(valor), 
                            descricao       = IF(VALUES(descricao) IS NOT NULL AND VALUES(descricao) != '', VALUES(descricao), descricao),
                            categoria       = VALUES(categoria), 
                            config_group    = VALUES(config_group), 
                            is_active       = VALUES(is_active)";
                
                $stmt = $this->db->prepare($sql);
                
                return $stmt->execute([
                    ':chave'     => $chave,
                    ':valor'     => $valor,
                    ':descricao' => $descricao,
                    ':categoria' => $categoria,
                    ':grupo'     => $grupo,
                    ':ativo'     => $ativo
                ]);
        }
    }