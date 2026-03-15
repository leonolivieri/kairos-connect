<?php
namespace src\Models;

use src\Config\Database;
use src\Helpers\SecurityHelper;
use PDO;

/**
 * NOME: ConfigRepository
 * CONCEITO: Data Access Object (DAO) com Blindagem Automática.
 * PORQUE O NOME: "Repository" serve como um mediador que centraliza todas as operações 
 * de CRUD da tabela 'kairos_configuracoes', isolando a complexidade do SQL.
 */
class ConfigRepository {
    private $db;
    
    /**
     * Lista de chaves que exigem criptografia.
     * CONCEITO: Whitelisting de Segurança. Apenas o que está aqui é cifrado.
     */
    private $sensitivas = ['META_ACCESS_TOKEN', 'META_VERIFY_TOKEN', 'IA_API_KEY', 'MASTER_KEY'];

    public function __construct() {
        // CONCEITO: Injeção de Dependência via Singleton.
        $this->db = Database::getInstance();
    }

    /**
     * MÉTODO: listarTodos
     * O QUE FAZ: Recupera todos os registros organizados por grupo.
     * CONCEITO: Visão Macro. Útil para alimentar tabelas de administração.
     */
    public function listarTodos() {
        $stmt = $this->db->query("SELECT * FROM kairos_configuracoes ORDER BY config_group, chave");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * MÉTODO: buscar
     * O QUE FAZ: Localiza uma configuração e a decifra se for sensível.
     * CONCEITO: Decodificação On-the-fly (em tempo real). O usuário recebe o dado limpo.
     */
    public function buscar($chave) {
        // Normalização: Garante que a busca ignore diferenças de caixa (A vs a) [cite: 2026-03-10]
        $chave = strtoupper(trim($chave)); 
        
        $stmt = $this->db->prepare("SELECT * FROM kairos_configuracoes WHERE chave = :chave");
        $stmt->execute([':chave' => $chave]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se a chave for sensitiva, aplicamos a decriptação antes de retornar
        if ($res && in_array($chave, $this->sensitivas)) {
            $res['valor'] = SecurityHelper::decrypt($res['valor']);
        }
        return $res;
    }

    /**
     * MÉTODO: salvar
     * O QUE FAZ: Insere ou atualiza uma configuração com blindagem automática.
     * CONCEITO: Upsert (Update or Insert). Resolve a criação e edição em um único comando.
     */
    public function salvar($dados) {
        $chave = strtoupper(trim($dados['chave']));
        $valor = $dados['valor'];

        // CONCEITO: Blindagem Preventiva. Criptografa antes de tocar no disco (DB) [cite: 2026-03-09]
        if (in_array($chave, $this->sensitivas)) {
            $valor = SecurityHelper::encrypt($valor);
        }

        // SQL Híbrido: Se a chave existir, atualiza; se não, insere. [cite: 2026-03-10]
        $sql = "INSERT INTO kairos_configuracoes (chave, valor, descricao, categoria, config_group, is_active) 
                VALUES (:chave, :valor, :descricao, :categoria, :grupo, :ativo)
                ON DUPLICATE KEY UPDATE 
                    valor = VALUES(valor),
                    descricao = VALUES(descricao),
                    config_group = VALUES(config_group),
                    is_active = VALUES(is_active)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':chave'     => $chave,
            ':valor'     => $valor,
            ':descricao' => $dados['descricao'] ?? '',
            ':categoria' => $dados['categoria'] ?? 'Sistema',
            ':grupo'     => $dados['config_group'] ?? 'Sistema',
            ':ativo'     => $dados['is_active'] ?? 1
        ]);
    }

    /**
     * MÉTODO: excluir
     * O QUE FAZ: Remove fisicamente uma configuração pelo nome da chave.
     */
    public function excluir($chave) {
        $stmt = $this->db->prepare("DELETE FROM kairos_configuracoes WHERE chave = :chave");
        return $stmt->execute([':chave' => strtoupper($chave)]);
    }
}