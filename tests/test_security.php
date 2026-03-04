<?php
/**
 * ARQUIVO: tests/test_security.php
 * OBJETIVO: Validar a blindagem AES-256 antes da implementação final.
 */

// 1. IMPORTAÇÃO DAS DEPENDÊNCIAS
// Como este arquivo está dentro da pasta /tests, precisamos subir um nível para achar o Helper
 require_once __DIR__ . '/../src/Helpers/SecurityHelper.php';

use App\Helpers\SecurityHelper;

// 2. O DADO PARA TESTE (SIMULAÇÃO)
// Imagine que este é o seu Token da API da Meta que não pode vazar de jeito nenhum.
$dadoOriginal = "TOKEN_SECRETO_WHATSAPP_KAIROS_2026";

echo "=== PROTOCOLO DE TESTE DE FOGO KAIRÓS ===\n\n";
echo "1. Dado Original: " . $dadoOriginal . "\n";

// 3. O PROCESSO DE BLINDAGEM (CRIPTOGRAFIA)
$dadoCifrado = SecurityHelper::encrypt($dadoOriginal);
echo "2. Dado Cifrado (Como ficará no Banco de Dados): \n   " . $dadoCifrado . "\n\n";

// 4. O PROCESSO DE REVELAÇÃO (DECRIPTOGRAFIA)
$dadoRecuperado = SecurityHelper::decrypt($dadoCifrado);
echo "3. Dado Recuperado pelo Sistema: " . $dadoRecuperado . "\n";

// 5. VERIFICAÇÃO DE INTEGRIDADE
echo "-------------------------------------------\n";
if ($dadoOriginal === $dadoRecuperado) {
    echo "✅ RESULTADO: SUCESSO. A blindagem está operacional e os dados estão íntegros.\n";
} else {
    echo "❌ RESULTADO: FALHA. O dado recuperado é diferente do original. Verifique sua MASTER_KEY.\n";
}
echo "-------------------------------------------\n";