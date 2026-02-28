<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\SecurityHelper;
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
     * Salva ou atualiza uma configuração com blindagem
     */
    public function set($chave, $valor, $grupo = 'Sistema') {
        // Se for sensível, encripta antes de salvar
        if ($grupo === 'WHATSAPP_API') {
            $valor = SecurityHelper::encrypt($valor);
        }

        $sql = "INSERT INTO kairos_configuracoes (chave, valor, config_group) 
                VALUES (:chave, :valor, :grupo) 
                ON DUPLICATE KEY UPDATE valor = :valor, config_group = :grupo";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':chave' => $chave,
            ':valor' => $valor,
            ':grupo' => $grupo
        ]);
    }
}