<?php
    /**
     * ARQUIVO: scripts/setup_configs.php
     * OBJETIVO: Sincronizar o .env com o DB via Bootstrap e Encriptação AES-256.
     * STATUS: Refinado (Topologia de Dois Saltos Corrigida e Carga Otimizada).
     */

    // 1. CARREGAMENTO DO SISTEMA (Topologia Absoluta: Dois Saltos)
    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';

    use src\Controllers\ConfigController;

    // 2. CAPTURA DAS SEÇÕES DO COFRE
    $meta_env = $_ENV['Ambiente_Meta'] ?? [];
    $ia_env   = $_ENV['Ambiente_IA']   ?? [];
    $dev_env  = $_ENV['Ambiente_Desenvolvimento'] ?? [];
    $op_env   = $_ENV['Ambiente_Operacao'] ?? []; 

    $config = new ConfigController();

    echo "=== KAIRÓS CONNECT: SINCRONIZANDO ATIVOS DO COFRE ===\n\n";

    // --- GRUPO: WHATSAPP_API ---
    // URLs e IDs (Públicos) = 0 | Tokens e Chaves = 1
    $config->set('META_BASE_URL',     $meta_env['META_BASE_URL'],   'URL Base da API Meta', 'Configuração', 'WHATSAPP_API', 0);
    $config->set('META_PHONE_ID',     $meta_env['META_PHONE_ID'],   'ID do número de telefone na Meta', 'Configuração', 'WHATSAPP_API', 0);
    $config->set('META_WABA_ID',      $meta_env['META_WABA_ID'],    'ID da Business Account (WABA)', 'Configuração', 'WHATSAPP_API', 0);
    
    // ATIVOS BLINDADOS
    $config->set('META_ACCESS_TOKEN', $meta_env['META_ACCESS_TOKEN'], 'Token de Acesso Permanente Meta', 'Configuração', 'WHATSAPP_API', 1);
    $config->set('META_VERIFY_TOKEN', $meta_env['META_VERIFY_TOKEN'], 'Token de Verificação do Webhook', 'Configuração', 'WHATSAPP_API', 1);

    // --- GRUPO: IA_CONFIG ---
    $config->set('IA_BASE_URL',      $ia_env['IA_BASE_URL'],      'URL Base da API Gemini', 'Configuração', 'IA_CONFIG', 0);
    $config->set('IA_MODEL',         $ia_env['IA_MODEL'],         'Modelo de IA em uso', 'Configuração', 'IA_CONFIG', 0);
    $config->set('IA_SYSTEM_PROMPT', 'Aja como um assistente executivo da Kairós Ventures.', 'Prompt de Personalidade', 'Sistema', 'IA_CONFIG', 0);
    
    // ATIVO BLINDADO
    $config->set('IA_API_KEY',       $ia_env['IA_API_KEY'],       'Chave do Google Gemini', 'Configuração', 'IA_CONFIG', 1);

    // --- GRUPO: OPERAÇÃO (Dados Internos e Localização) ---
    $codigo_pais = $op_env['COUNTRY_CODE'] ?? '55';
    $config->set('COUNTRY_CODE', $codigo_pais, 'DDI - Código do País Padrão', 'Sistema', 'OPERAÇÃO', 0);
    
    $config->set('IS_IA_ACTIVE', '1', 'Status Global da IA (0 ou 1)', 'Sistema', 'OPERAÇÃO', 0);
    $config->set('DEBUG_MODE',   '1', 'Nível de log detalhado (0 ou 1)', 'Sistema', 'OPERAÇÃO', 0);

    // --- GRUPO: SEGURANÇA ---
    $config->set('MASTER_KEY', $dev_env['MASTER_KEY'], 'Chave Mestra de Criptografia', 'Sistema', 'SEGURANÇA', 1);

    // --- GRUPO: EXPEDIENTE ---
    $config->set('HORARIO_INICIO', '08:00', 'Início do Expediente', 'Sistema', 'EXPEDIENTE', 0);
    $config->set('HORARIO_FIM',   '18:00', 'Fim do Expediente', 'Sistema', 'EXPEDIENTE', 0);

    echo "\n✅ SINCRONIZAÇÃO CONCLUÍDA COM SUCESSO!\n";