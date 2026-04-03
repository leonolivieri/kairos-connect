<?php
/**
 * FICHEIRO: src/Controllers/AuthController.php
 * OBJETIVO: Lógica de Negócios para Autenticação e Carregamento Multi-Workspace.
 * STATUS: Fase 5.1 - Refinamento de Contexto SaaS.
 * VERSÃO: 2.1 (Suporte a múltiplos Workspaces / Átrio de Seleção)
 * DATA DE CRIAÇÃO: 01 de Abril de 2026
 * ÚLTIMA ALTERAÇÃO: 03 de Abril de 2026
 * AUTOR: Engenharia Kairós (Leon)
 */

namespace src\Controllers;

use src\Config\Database;
use src\Helpers\SecurityHelper;
use Exception;

class AuthController {
    
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function tentarLogin($email, $senha) {
        try {
            // 1. Valida o Humano
            $stmt = $this->db->prepare("SELECT id, nome, senha FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && SecurityHelper::verifyPassword($senha, $user['senha'])) {
                
                // 2. Procura TODOS os Workspaces onde o humano tem acesso (Removemos o LIMIT 1)
                $stmtWs = $this->db->prepare("
                    SELECT w.id, w.nome_empresa, w.alias, wm.role 
                    FROM workspace_members wm
                    JOIN workspaces w ON wm.workspace_id = w.id
                    WHERE wm.user_id = :user_id
                ");
                $stmtWs->execute([':user_id' => $user['id']]);
                
                // FetchAll captura todos os resultados num array
                $workspaces = $stmtWs->fetchAll();

                return [
                    'sucesso' => true,
                    'user' => $user,
                    'workspaces' => $workspaces // Agora devolve um array de empresas
                ];
            }
            return ['sucesso' => false, 'mensagem' => 'Acesso negado. Credenciais inválidas.'];
            
        } catch (Exception $e) {
            return ['sucesso' => false, 'mensagem' => 'Ocorreu um erro interno no servidor.'];
        }
    }
}