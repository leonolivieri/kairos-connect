<?php
/**
 * =========================================================================
 * PROJETO: Kairós Connect
 * ARQUIVO: public/api_omni.php
 * OBJETIVO: Endpoint de API para o frontend Omnichannel
 * VERSÃO: 1.0.0
 * DATA/HORA: 18/03/2026 - 17:55
 * IMPLEMENTAÇÃO: Recebe os pedidos do omni.js e devolve os dados JSON
 * fornecidos pelo OmniController.
 * =========================================================================
 */

// 1. Carrega o mapa do sistema e o autoloader
require_once __DIR__ . '/../../kairos-connect/bootstrap.php';

use src\Controllers\OmniController;

// 2. Blindagem de Cabeçalho: Força o navegador a interpretar como JSON
header('Content-Type: application/json; charset=utf-8');

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

// 3. Instancia o Maestro
$controller = new OmniController();

// 4. Devolve a lista de conversas e encerra imediatamente
echo $controller->listarConversas();
exit;