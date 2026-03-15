<?php
namespace src\Models;

use src\Config\Database; // CORREÇÃO: "Config" com C maiúsculo (Blindagem Linux)
use PDO;
use Exception;

/**
 * CLASSE: Mensagem
 * OBJETIVO: Gerenciar a persistência de mensagens na tabela kairos_mensagens.
 * STATUS: Homologado com Blindagem de Transação e Mão-Dupla (Entrada/Saída).
 */
class Mensagem {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Salva uma mensagem no banco de dados.
     * @param string $direcao 'ENTRADA' (Cliente) ou 'SAIDA' (Kairós/IA)
     */
    public function salvar($whatsappId, $numero, $nome, $texto, $direcao = 'ENTRADA') {
        try {
            $sql = "INSERT INTO kairos_mensagens 
                    (cliente_id, whatsapp_id, remetente_numero, remetente_nome, mensagem_texto, direcao, status_leitura, created_at) 
                    VALUES (1, :wid, :num, :nome, :txt, :dir, 0, :dt)";
            
            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                ':wid'  => $whatsappId,
                ':num'  => $numero,
                ':nome' => $nome,
                ':txt'  => $texto,
                ':dir'  => $direcao,
                ':dt'   => date('Y-m-d H:i:s')
            ]);

            return true;

        } catch (Exception $e) {
            // Se o banco falhar, o Webhook não pode colapsar. Retornamos false e o processo segue.
            error_log("Erro no DB Kairós (Mensagem): " . $e->getMessage());
            return false;
        }
    }
}