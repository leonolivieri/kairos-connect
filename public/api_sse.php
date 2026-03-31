<?php
// Desliga o buffer automático e libera a trava de sessão
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

// Cabeçalhos obrigatórios para manter o túnel SSE aberto
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Evita que o Nginx bloqueie o fluxo

require_once __DIR__ . '/../../kairos-connect/bootstrap.php';
use src\Controllers\OmniController;

$omni = new OmniController();

// Túnel Persistente
while (true) {
    // Se o cliente fechar a aba, o servidor mata o loop
    if (connection_aborted()) {
        break;
    }

    $dados = $omni->listarConversas();
    
    // Padrão do protocolo SSE: "data: {json} \n\n"
    echo "data: " . $dados . "\n\n";
    
    // Força o envio imediato da carga para o navegador
    @ob_flush();
    @flush();
    
    // Respira por 3 segundos antes do próximo envio
    sleep(3);
}