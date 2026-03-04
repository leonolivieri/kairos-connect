<?php
/**
 * ARQUIVO: scripts/setup_configs.php
 * OBJETIVO: Sincronizar as credenciais do .env com o Banco de Dados.
 * * INSTRUÇÕES: 
 * 1. As chaves são lidas automaticamente do seu arquivo .env.
 * 2. Execute via terminal: php scripts/setup_configs.php
 * 3. O Controller cuidará da encriptação AES-256 antes de salvar.
 */

// 1. AJUSTE DE CAMINHOS
require_once __DIR__ . '/../src/Config/Database.php';
require_once __DIR__ . '/../src/Helpers/SecurityHelper.php';
require_once __DIR__ . '/../src/Controllers/ConfigController.php';

use App\Controllers\ConfigController;

// 2. CARREGAMENTO DO COFRE (.env)
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    die("Erro Crítico: Arquivo .env não encontrado em $envPath\n");
}
$env = parse_ini_file($envPath, true);

// Nota: No seu .env, as chaves da Meta estão logo após [Ambiente_Local], 
// portanto o parse_ini_file as coloca dentro dessa seção ou na raiz 
// dependendo da versão. Vamos garantir a captura:
$meta = $env['Ambiente_Local'] ?? $env;

$config = new ConfigController();

echo "=== KAIRÓS CONNECT: SINCRONIZANDO ATIVOS DO COFRE ===\n\n";

// --- GRUPO: WHATSAPP_API ---
echo "[+] Sincronizando WHATSAPP_API...\n";
$config->set('meta_phone_id',     $meta['META_PHONE_ID'], 'ID do número de telefone na Meta', 'Configuração', 'WHATSAPP_API', 1);
$config->set('meta_waba_id',      $meta['META_WABA_ID'],  'ID da Business Account (WABA)',   'Configuração', 'WHATSAPP_API', 1);
$config->set('meta_access_token', $meta['META_ACCESS_TOKEN'], 'Token de Acesso Permanente Meta', 'Configuração', 'WHATSAPP_API', 1);
$config->set('meta_verify_token', $meta['META_VERIFY_TOKEN'], 'Token de Verificação do Webhook', 'Configuração', 'WHATSAPP_API', 1);

// --- GRUPO: IA_CONFIG ---
echo "[+] Configurando IA_CONFIG...\n";
// Dica: Adicione OPENAI_API_KEY ao seu .env para automatizar aqui também
$openai_key = $meta['OPENAI_API_KEY'] ?? 'CHAVE_NAO_CONFIGURADA_NO_ENV';
$config->set('openai_api_key', $openai_key, 'Chave Privada da OpenAI', 'Configuração', 'IA_CONFIG', 1);
$config->set('ia_system_prompt', 'Aja como um assistente executivo da Kairós Ventures. Seja direto e profissional.', 'Prompt de Personalidade da IA', 'Sistema', 'IA_CONFIG', 1);

// --- GRUPO: EXPEDIENTE ---
echo "[+] Configurando EXPEDIENTE...\n";
$config->set('horario_inicio', '08:00', 'Horário de Início do Expediente', 'Sistema', 'EXPEDIENTE', 1);
$config->set('horario_fim', '18:00', 'Horário de Término do Expediente', 'Sistema', 'EXPEDIENTE', 1);

echo "\n✅ SINCRONIZAÇÃO CONCLUÍDA COM SUCESSO!\n";
echo "Os dados foram encriptados e salvos no banco local 'kairos'.\n";