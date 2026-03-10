<?php
namespace src\Models;

use src\config\Database;
use PDO;

/**
 * CLASSE: Mensagem
 * OBJETIVO: Gerenciar a persistência de mensagens na tabela kairos_mensagens.
 */
class Mensagem {
    private $db;

    public function __construct() {
        // Utilizamos a instância única de conexão que já mapeamos no seu projeto
        $this->db = Database::getInstance();
    }

    /**
     * Salva uma nova mensagem recebida no banco de dados.
     */
    public function salvar($whatsappId, $numero, $nome, $texto) {
        $sql = "INSERT INTO kairos_mensagens 
                (cliente_id, whatsapp_id, remetente_numero, remetente_nome, mensagem_texto, direcao, status_leitura,created_at) 
                VALUES (1, :wid, :num, :nome, :txt, 'ENTRADA', 0, :dt)";

        $stmt = $this->db->prepare($sql);
        
        // Vinculamos os dados às colunas da sua tabela
        $stmt->bindParam(':wid' , $whatsappId);
        $stmt->bindParam(':num' , $numero);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':txt' , $texto);
        $stmt->bindParam(':dt'  , date('Y-m-d H:i:s'));

        return $stmt->execute();
    }
}