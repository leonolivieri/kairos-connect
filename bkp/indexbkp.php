<?php
/**
 * ARQUIVO: public/index.php
 * OBJETIVO: Hub Central de Navegação do Kairós Connect.
 * STATUS: Operacional (Verificação de Drift Alinhada com a Doutrina Kairós).
 */

use src\Config\Database;
require_once __DIR__ . '/../../kairos-connect/bootstrap.php';

$alerta_drift = false;

try {
    $db = Database::getInstance();
    
    // ARQUITETURA ALINHADA: Mesma matriz de validação do admin_configs.php
    $ativos_vitais = ['META_PHONE_ID', 'META_WABA_ID', 'META_ACCESS_TOKEN', 'META_VERIFY_TOKEN', 'IA_API_KEY'];
    
    $stmt_check = $db->query("SELECT chave FROM kairos_configuracoes");
    $chaves_banco = $stmt_check->fetchAll(PDO::FETCH_COLUMN);
    
    $chaves_banco_upper = array_map('strtoupper', $chaves_banco);

    foreach ($ativos_vitais as $vital) {
        if (!in_array(strtoupper($vital), $chaves_banco_upper)) {
            $alerta_drift = true; 
            break; // Se faltar uma, já acende o alerta no Hub
        }
    }
} catch (Exception $e) {
    $alerta_drift = true; // Se o banco falhar, o cofre precisa de atenção
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kairós Connect - Hub Central</title>
    <!-- Quebra de Cache -->
    <link rel="stylesheet" href="css/style.css?v=1.4">
    <style>
        body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; color: #333; }
        .topbar { background-color: #0b1f2d; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .topbar h1 { margin: 0; font-size: 1.5rem; color: #00a8ff; font-weight: 300; letter-spacing: 1px; }
        .topbar .status { font-size: 0.85rem; color: #8bb1c4; }
        
        .dashboard { padding: 40px 30px; display: flex; gap: 20px; flex-wrap: wrap; }
        
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 25px; width: 300px; display: flex; flex-direction: column; position: relative; transition: transform 0.2s; border: 1px solid #e1e8ed; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
        
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .card-icon { font-size: 1.5rem; }
        .card-title { font-size: 1.1rem; font-weight: bold; margin: 0; color: #1a1a1a; }
        
        .badge-warning { background-color: #f59e0b; color: white; font-size: 0.7rem; padding: 4px 8px; border-radius: 12px; font-weight: bold; }
        
        .card-desc { font-size: 0.85rem; color: #6c757d; line-height: 1.5; flex-grow: 1; margin-bottom: 20px; }
        
        .card-action { text-decoration: none; font-size: 0.85rem; font-weight: bold; color: #1a5f7a; text-transform: uppercase; }
        .card-action:hover { color: #00a8ff; }
        
        .card.disabled { opacity: 0.6; cursor: not-allowed; }
        .card.disabled:hover { transform: none; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .card.disabled .card-action { color: #999; }
    </style>
</head>
<body>

    <div class="topbar">
        <h1>KAIRÓS CONNECT</h1>
        <div class="status">Motor de Integração Híbrida | Status: Online</div>
    </div>

    <div class="dashboard">
        <!-- MÓDULO 1: COFRE -->
        <div class="card">
            <div class="card-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="card-icon">🔐</span>
                    <h2 class="card-title">Cofre de Ativos</h2>
                </div>
                <?php if ($alerta_drift): ?>
                    <span class="badge-warning">⚠️ Requer Atenção</span>
                <?php endif; ?>
            </div>
            <p class="card-desc">Gerenciamento de chaves de API, senhas, tokens da Meta e variáveis globais do sistema com blindagem AES-256.</p>
            <a href="admin_configs.php" class="card-action">ACESSAR MÓDULO ➔</a>
        </div>

        <!-- MÓDULO 2: LOGS -->
        <div class="card">
            <div class="card-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="card-icon">💬</span>
                    <h2 class="card-title">Logs do WhatsApp</h2>
                </div>
            </div>
            <p class="card-desc">Monitoramento em tempo real dos webhooks. Rastreio de mensagens enviadas e recebidas pela infraestrutura da Kairós.</p>
            <a href="painel.php" class="card-action">ACESSAR MÓDULO ➔</a>
        </div>

        <!-- MÓDULO 3: IA -->
        <div class="card disabled">
            <div class="card-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="card-icon">🧠</span>
                    <h2 class="card-title">Centro Cognitivo</h2>
                </div>
            </div>
            <p class="card-desc">Ajuste de prompts dinâmicos, controle de temperatura da IA e auditoria de respostas.</p>
            <span class="card-action" style="cursor: default;">EM BREVE (FASE 3)</span>
        </div>
    </div>

</body>
</html>