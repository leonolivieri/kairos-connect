<?php
    /**
     * FICHEIRO: public/trocar_workspace.php
     * OBJETIVO: Ponte para levar o utilizador logado de volta ao Átrio de Seleção.
     * STATUS: Fase 5.2 - Workspace Switcher.
     * VERSÃO: 1.0 (Motor de Transição)
     * DATA DE CRIAÇÃO: 03 de Abril de 2026
     * AUTOR: Engenharia Kairós (Leon)
     */

    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';
    
    use src\Config\Database;

    session_start();

    // Segurança: Só passa quem já está logado
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    try {
        $db = Database::getInstance();
        
        // Vai buscar novamente todos os Workspaces do utilizador
        $stmtWs = $db->prepare("
            SELECT w.id, w.nome_empresa, w.alias, wm.role 
            FROM workspace_members wm
            JOIN workspaces w ON wm.workspace_id = w.id
            WHERE wm.user_id = :user_id
        ");
        $stmtWs->execute([':user_id' => $_SESSION['user_id']]);
        $workspaces = $stmtWs->fetchAll();

        // Se tiver mais de um, cria a variável temporária e abre a porta do Átrio
        if (count($workspaces) > 1) {
            $_SESSION['temp_workspaces'] = $workspaces;
            header("Location: escolher_workspace.php");
            exit;
        } else {
            // Se só tiver um, não há para onde trocar, volta para o Hub
            header("Location: index.php");
            exit;
        }

    } catch (Exception $e) {
        // Em caso de falha de BD, aborta e volta ao Hub
        header("Location: index.php");
        exit;
    }
?>