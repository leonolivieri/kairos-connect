<?php
/**
 * ARQUIVO: scripts/setup_configs.php
 * OBJETIVO: Alimentar o banco de dados com credenciais REAIS já encriptadas.
 * * INSTRUÇÕES: 
 * 1. Preencha os valores abaixo com seus dados reais da Meta e OpenAI.
 * 2. Execute via terminal: php scripts/setup_configs.php
 * 3. Verifique o banco de dados: o campo 'valor' deve estar ilegível (criptografado).
 */

// 1. AJUSTE DE CAMINHOS (Autoload Simulado)
require_once __DIR__ . '/../src/Config/Database.php';
require_once __DIR__ . '/../src/Helpers/SecurityHelper.php';
require_once __DIR__ . '/../src/Controllers/ConfigController.php';

use App\Controllers\ConfigController;

$config = new ConfigController();

echo "=== KAIRÓS CONNECT: INICIANDO CARGA DE CONFIGURAÇÕES ===\n\n";

// --- GRUPO: WHATSAPP_API (Serão encriptados automaticamente pelo Controller) ---

echo "[+] Configurando WHATSAPP_API...\n";
$config->set('meta_phone_id', '949457254926583', 'WHATSAPP_API');
$config->set('meta_waba_id', '1289128319941058', 'WHATSAPP_API');
$config->set('meta_access_token', 'EAAd8IvrhpuYBQ1DdZCwpq7Iyhmnqunp231tCxRuXgScYJVmBC7evdNpx8lHhnfxIgj2pyS7LuCvUHsNNbRkZC8k1yDWAzXDVZCTQ2egwElQ367pCYrikKuzpknSrRGX398XtP3NZCsZCjdasMfUDC8H20hePbhFi9GEoGvaqtOjEZCyQnrqJSEsjtCYZB2IEsSW0etYZCyaV4HSKekhp7dZBMhGGZCuQxz9lBlcP2e5QzjZCos3eyQ09g5sZCAQA34eajbUUfN8A7OTubMZCISKuyOyPS', 'WHATSAPP_API');
$config->set('meta_verify_token', 'KAIROS_LAB_2026', 'WHATSAPP_API');

// --- GRUPO: IA_CONFIG (Dados estratégicos) ---

echo "[+] Configurando IA_CONFIG...\n";
$config->set('openai_api_key', 'SUA_CHAVE_OPENAI_AQUI', 'WHATSAPP_API'); // Encriptamos também por segurança
$config->set('ia_system_prompt', 'Aja como um assistente executivo da Kairós Ventures. Seja direto e profissional.', 'IA_CONFIG');

// --- GRUPO: EXPEDIENTE (Dados públicos/comuns) ---

echo "[+] Configurando EXPEDIENTE...\n";
$config->set('horario_inicio', '08:00', 'EXPEDIENTE');
$config->set('horario_fim', '18:00', 'EXPEDIENTE');

echo "\n✅ CARGA CONCLUÍDA COM SUCESSO!\n";
echo "Vá ao seu Banco de Dados e observe que os Tokens estão protegidos.\n";