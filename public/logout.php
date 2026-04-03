<?php
    /**
     * FICHEIRO: public/logout.php
     * OBJETIVO: Destruição Absoluta de Sessão (Logout Seguro).
     * STATUS: Fase 4 - Encerramento do Ciclo de Vida da Autenticação.
     * VERSÃO: 1.0 (Destruição em 3 Camadas)
     * DATA DE CRIAÇÃO: 02 de Abril de 2026
     * AUTOR: Engenharia Kairós (Leon)
    */

    session_start();

    // Camada 1: Esvazia a memória (Zera as variáveis globais da sessão)
    $_SESSION = array();

    // Camada 2: Obliterar o Cookie de Sessão no Navegador do Utilizador
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Camada 3: Destrói o ficheiro físico no servidor Hostinger
    session_destroy();

    // Redireciona de volta para a rua (Ecrã de Login)
    header("Location: login.php");
    exit;