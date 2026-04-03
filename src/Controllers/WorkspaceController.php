<?php
/**
 * FICHEIRO: src/Controllers/WorkspaceController.php
 * OBJETIVO: Gerir a lógica de negócios do Workspace (Gerar convites, validar membros).
 * STATUS: Fase 5 - Arquitetura de Convites SaaS.
 * VERSÃO: 1.0 (Migração de código monolítico para MVC)
 * DATA DE CRIAÇÃO: 02 de Abril de 2026
 * AUTOR: Engenharia Kairós (Leon)
 */

namespace src\Controllers;

use src\Config\Database;
use Exception;

class WorkspaceController {
    
    private $db;

    public function __construct() {
        // Conecta à Base de Dados
        $this->db = Database::getInstance();
    }

    /**
     * Gera um link mágico para convidar um utilizador para o Workspace
     */
    public function gerarConvite($ws_id, $email, $role) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['sucesso' => false, 'mensagem' => 'O formato do e-mail é inválido.'];
        }

        try {
            // 1. Verifica se o convidado já está na equipa
            $stmtCheck = $this->db->prepare("
                SELECT wm.id FROM workspace_members wm
                JOIN usuarios u ON wm.user_id = u.id
                WHERE wm.workspace_id = :ws_id AND u.email = :email
            ");
            $stmtCheck->execute([':ws_id' => $ws_id, ':email' => $email]);
            
            if ($stmtCheck->fetch()) {
                return ['sucesso' => false, 'mensagem' => 'Este utilizador já faz parte da sua equipa.'];
            }

            // 2. Gera o Token (Bilhete Dourado)
            $token = bin2hex(random_bytes(32)); 
            $expires_at = date('Y-m-d H:i:s', strtotime('+48 hours'));

            // 3. Guarda o convite na base de dados
            $stmtInv = $this->db->prepare("
                INSERT INTO workspace_invites (workspace_id, email, token, role, expires_at) 
                VALUES (:ws_id, :email, :token, :role, :expires)
            ");
            
            $stmtInv->execute([
                ':ws_id' => $ws_id,
                ':email' => $email,
                ':token' => $token,
                ':role'  => $role,
                ':expires'=> $expires_at
            ]);

            // 4. Monta o Link Mágico
            $protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
            $dominio = $_SERVER['HTTP_HOST'];
            $link_magico = $protocolo . $dominio . "/aceitar_convite.php?token=" . $token;

            return [
                'sucesso' => true, 
                'mensagem' => 'Convite gerado com sucesso!', 
                'link' => $link_magico
            ];

        } catch (Exception $e) {
            return ['sucesso' => false, 'mensagem' => 'Erro interno ao gerar convite.'];
        }
    }
}