<?php



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

    /**
     * Obtém uma configuração decifrada (se necessário)
     * @param string $chave Chave da configuração
     * @return string Valor processado
     */
    public function get($chave) {
        $stmt = $this->db->prepare("SELECT valor, config_group FROM kairos_configuracoes WHERE chave = :chave LIMIT 1");
        $stmt->execute([':chave' => $chave]);
        $result = $stmt->fetch();

        if (!$result) return null;

        // Se for do grupo WHATSAPP_API, tentamos decifrar
        if ($result['config_group'] === 'WHATSAPP_API') {
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
        if ($grupo === 'WHATSAPP_API' || $chave === 'openai_api_key') {
            $valor = SecurityHelper::encrypt($valor);
        }

        // Mudança sutil: trocamos ":valor" por "VALUES(valor)" no UPDATE
        $sql = "INSERT INTO kairos_configuracoes (chave, valor, descricao, categoria, config_group, is_active) 
                VALUES (:chave, :valor, :descricao, :categoria, :grupo, :ativo) 
                ON DUPLICATE KEY UPDATE 
                    valor = VALUES(valor), 
                    descricao = VALUES(descricao), 
                    categoria = VALUES(categoria), 
                    config_group = VALUES(config_group), 
                    is_active = VALUES(is_active)";
        
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