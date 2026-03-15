<?php
/**
 * ARQUIVO: scripts/test_chat.php
 * OBJETIVO: Testar o ChatController com chaves UPPERCASE e criptografia.
 */

// 1. Carregamento do Motor Kairós [cite: 2026-03-10]
require_once __DIR__ . '/../bootstrap.php';

use src\Controllers\ChatController;

// 2. Configuração de Log para o teste
$logFile = __DIR__ . '/../logs/test_chat.log';
if (!is_dir(__DIR__ . '/../logs')) mkdir(__DIR__ . '/../logs');

$chat = new ChatController();

echo "=== KAIRÓS CONNECT: SIMULADOR DE WEBHOOK ===\n";
echo "[+] Iniciando processamento de mensagem de teste...\n";

// 3. Simulação de Parâmetros de Entrada
$whatsappId = 'TEST-' . time();
$numero     = '5519999999999';
$nome       = 'Leon (Teste de Mesa)';
$texto      = 'Olá Arquiteto, você está conseguindo ler as chaves criptografadas do banco?';

try {
    // Executa a lógica do Controller
    $resposta = $chat->processarMensagem($whatsappId, $numero, $nome, $texto, $logFile);
    
    echo "\n[RESPOSTA DA IA]:\n";
    echo "--------------------------------------------------\n";
    echo $resposta . "\n";
    echo "--------------------------------------------------\n";
    echo "\n✅ TESTE CONCLUÍDO. Verifique o log em: logs/test_chat.log\n";

} catch (Exception $e) {
    echo "\n❌ ERRO CRÍTICO NO TESTE: " . $e->getMessage() . "\n";
}