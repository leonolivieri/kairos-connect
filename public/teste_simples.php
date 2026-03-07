<?php
/**
 * ARQUIVO: teste_simples.php
 * ESTRATÉGIA: Força Bruta para Localização de Ativo
 */

// 1. O caminho absoluto que vimos na sua imagem 16f782.png
$path = __DIR__ . '/../../kairos-connect/src/config/database.php';

echo "=== PROTOCOLO DE CONEXÃO KAIRÓS ===<br><br>";
echo "Buscando: " . $path . "<br>";

if (file_exists($path)) {
    echo "✅ ARQUIVO ENCONTRADO: O arquivo existe no caminho especificado.<br>";
} else {
    echo "❌ ARQUIVO NÃO ENCONTRADO: O arquivo não existe no caminho especificado.<br>";
    exit;
}
// 2. Teste de Acesso Real
if (is_readable($path)) {
    echo "✅ SUCESSO: O arquivo existe e é LEITURA permitida!<br>";
    
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "Carregando motor do sistema...<br>";
    require_once $path;
    echo "✅ CONECTADO: A classe Database foi incorporada com sucesso.";
} else {
    echo "❌ BLOQUEIO: O arquivo não foi encontrado ou o servidor negou o acesso.<br>";
    echo "DICA: Verifique se o nome da pasta 'kairos-connect' tem algum espaço no final.";
}