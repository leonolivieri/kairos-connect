<?php
/**
 * FICHEIRO: src/Controllers/InviteController.php
 * OBJETIVO: Validação de Tokens de Convite e Registo de Novos Membros (SaaS).
 * STATUS: Fase 5 - Arquitetura de Convites.
 * VERSÃO: 1.0 (Validação de Token e Transação de Aceitação)
 * DATA DE CRIAÇÃO: 02 de Abril de 2026
 * AUTOR: Engenharia Kairós (Leon)
 */

namespace src\Controllers;

use src\Config\Database;
use src\Helpers\SecurityHelper;
use Exception;

class InviteController {
    
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Valida se o Token existe, pertence a um Workspace e não expirou
     */
    public function validarToken($token) {
        try {
            $stmt = $this->db->prepare("
                SELECT wi.*, w.nome_empresa 
                FROM workspace_invites wi 
                JOIN workspaces w ON wi.workspace_id = w.id 
                WHERE wi.token = :token AND wi.status = 'PENDING' 
                LIMIT 1
            ");
            $stmt->execute([':token' => $token]);
            $convite = $stmt->fetch();

            if (!$convite) {
                return ['valido' => false, 'mensagem' => 'Convite inválido, cancelado ou já utilizado.'];
            }

            // Verifica a Ampulheta (Expiração)
            if (strtotime($convite['expires_at']) < time()) {
                // Queima o bilhete
                $this->db->prepare("UPDATE workspace_invites SET status = 'EXPIRED' WHERE id = :id")->execute([':id' => $convite['id']]);
                return ['valido' => false, 'mensagem' => 'Este convite expirou. Peça ao administrador um novo link.'];
            }

            return ['valido' => true, 'convite' => $convite];

        } catch (Exception $e) {
            return ['valido' => false, 'mensagem' => 'Erro interno ao validar o bilhete.'];
        }
    }

    /**
     * Processa o formulário de aceitação do convidado
     */
    public function processarAceitacao($token, $nome, $senha) {
        $valida = $this->validarToken($token);
        if (!$valida['valido']) {
            return ['sucesso' => false, 'mensagem' => $valida['mensagem']];
        }

        $convite = $valida['convite'];

        try {
            // BOLHA TEMPORAL (TRANSAÇÃO)
            $this->db->beginTransaction();

            // 1. Verifica se o e-mail já existe no ecossistema
            $stmtUser = $this->db->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
            $stmtUser->execute([':email' => $convite['email']]);
            $user = $stmtUser->fetch();

            if ($user) {
                // O utilizador já existe, apenas capturamos o ID dele
                $userId = $user['id'];
            } else {
                // Utilizador novo: Inserimos na base com a senha escolhida
                $senha_hash = SecurityHelper::hashPassword($senha);
                $stmtInsert = $this->db->prepare("INSERT INTO usuarios (nome, email, senha, auth_provider) VALUES (:nome, :email, :senha, 'local')");
                $stmtInsert->execute([':nome' => $nome, ':email' => $convite['email'], ':senha' => $senha_hash]);
                $userId = $this->db->lastInsertId();
            }

            // 2. Liga o Funcionário ao Workspace
            $stmtMember = $this->db->prepare("INSERT INTO workspace_members (user_id, workspace_id, role) VALUES (:u_id, :ws_id, :role)");
            $stmtMember->execute([
                ':u_id' => $userId, 
                ':ws_id' => $convite['workspace_id'],
                ':role' => $convite['role']
            ]);

            // 3. Queima o Bilhete (Marca como Aceite)
            $stmtUpdate = $this->db->prepare("UPDATE workspace_invites SET status = 'ACCEPTED' WHERE id = :id");
            $stmtUpdate->execute([':id' => $convite['id']]);

            $this->db->commit();
            return ['sucesso' => true, 'mensagem' => 'Conta ativada com sucesso! Bem-vindo(a) à equipa.'];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['sucesso' => false, 'mensagem' => 'Erro ao processar a sua entrada na equipa.'];
        }
    }
}