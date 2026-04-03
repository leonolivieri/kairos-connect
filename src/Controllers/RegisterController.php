<?php
/**
 * FICHEIRO: src/Controllers/RegisterController.php
 * OBJETIVO: Criação B2B (Frictionless Onboarding) de Usuário e Workspace.
 * AUTOR: Engenharia Kairós (Leon)
 */

namespace src\Controllers;

use src\Config\Database;
use src\Helpers\SecurityHelper;
use Exception;

class RegisterController {
    
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function registrarNovaEmpresa($nome, $email, $senha) {
        try {
            // Validação de Unicidade
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            
            if ($stmt->fetch()) {
                return ['sucesso' => false, 'mensagem' => 'Este e-mail já está registado. Aceda ao Login.'];
            }

            // ==================================================
            // TRANSAÇÃO ACID: Cria o Humano e a Empresa
            // ==================================================
            $this->db->beginTransaction();

            try {
                // 1. Cria Humano
                $senha_hash = SecurityHelper::hashPassword($senha);
                $stmtUser = $this->db->prepare("INSERT INTO usuarios (nome, email, senha, auth_provider) VALUES (:nome, :email, :senha, 'local')");
                $stmtUser->execute([':nome' => $nome, ':email' => $email, ':senha' => $senha_hash]);
                $userId = $this->db->lastInsertId();

                // 2. Auto-Gera Workspace
                $primeiro_nome = explode(' ', trim($nome))[0];
                $nome_ws = "Workspace de " . $primeiro_nome;
                $alias = 'krs_' . bin2hex(random_bytes(4)); 
                
                $stmtWs = $this->db->prepare("INSERT INTO workspaces (nome_empresa, plano_assinatura, alias) VALUES (:nome, 'Trial', :alias)");
                $stmtWs->execute([':nome' => $nome_ws, ':alias' => $alias]);
                $workspaceId = $this->db->lastInsertId();

                // 3. Liga Humano -> Workspace (OWNER)
                $stmtMember = $this->db->prepare("INSERT INTO workspace_members (user_id, workspace_id, role) VALUES (:u_id, :ws_id, 'OWNER')");
                $stmtMember->execute([':u_id' => $userId, ':ws_id' => $workspaceId]);

                $this->db->commit();
                return ['sucesso' => true, 'mensagem' => 'O seu Workspace foi criado! Inicie sessão.'];

            } catch (Exception $e) {
                $this->db->rollBack();
                return ['sucesso' => false, 'mensagem' => 'Falha crítica ao gerar Ecossistema. Tente novamente.'];
            }
        } catch (Exception $e) {
            return ['sucesso' => false, 'mensagem' => 'Erro interno de servidor.'];
        }
    }
}