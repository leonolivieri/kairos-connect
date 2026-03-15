<?php
/**
 * ARQUIVO: public/health_check.php
 * OBJETIVO: Centro de Comando e Observabilidade Kairós.
 * STATUS: Fase 3 - Visão Gerencial.
 * VERSÃO: 1.0
 * DATA DE CRIAÇÃO: 14 de Março de 2026, 11:35 (BRT)
 * ÚLTIMA ALTERAÇÃO: 14 de Março de 2026, 11:49 (BRT)
 * AUTOR: Engenharia Kairós (Leon)
 */

use src\Config\Database;
require_once __DIR__ . '/../../kairos-connect/bootstrap.php';

$resultados = [
    'banco' => ['status' => 'Pendente', 'msg' => '', 'cor' => 'bg-gray-200'],
    'meta' => ['status' => 'Pendente', 'msg' => '', 'cor' => 'bg-gray-200'],
    'gemini' => ['status' => 'Pendente', 'msg' => '', 'cor' => 'bg-gray-200']
];

$configs = [];

// ==========================================
// 1. TESTE DE BANCO DE DADOS E COLETA DE ATIVOS
// ==========================================
try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT chave, valor FROM kairos_configuracoes");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $configs[$row['chave']] = $row['valor'];
    }
    $resultados['banco'] = ['status' => 'Operacional', 'msg' => 'Conexão estável e ativos carregados.', 'cor' => 'bg-green-100 text-green-800 border-green-300'];
} catch (Exception $e) {
    $resultados['banco'] = ['status' => 'Falha Crítica', 'msg' => $e->getMessage(), 'cor' => 'bg-red-100 text-red-800 border-red-300'];
}

// ==========================================
// 2. TESTE DE INTEGRIDADE META (WHATSAPP)
// ==========================================
if ($resultados['banco']['status'] == 'Operacional') {
    $metaToken = $configs['META_ACCESS_TOKEN'] ?? '';
    $phoneId = $configs['META_PHONE_ID'] ?? '';

    if (empty($metaToken) || empty($phoneId)) {
        $resultados['meta'] = ['status' => 'Incompleto', 'msg' => 'Token ou Phone ID ausentes no banco.', 'cor' => 'bg-yellow-100 text-yellow-800 border-yellow-300'];
    } else {
        // Ping na API da Meta
        $urlMeta = "https://graph.facebook.com/v18.0/$phoneId";
        $ch = curl_init($urlMeta);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $metaToken"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resMeta = curl_exec($ch);
        $httpCodeMeta = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $resDecoded = json_decode($resMeta, true);

        if ($httpCodeMeta == 200 && isset($resDecoded['id'])) {
            $resultados['meta'] = ['status' => 'Operacional', 'msg' => 'Token válido. Permissão de envio ativa.', 'cor' => 'bg-green-100 text-green-800 border-green-300'];
        } else {
            $erro = $resDecoded['error']['message'] ?? 'Erro desconhecido';
            $resultados['meta'] = ['status' => 'Falha Crítica', 'msg' => 'Token Expirado ou Inválido. Detalhe: ' . $erro, 'cor' => 'bg-red-100 text-red-800 border-red-300'];
        }
    }
}

// ==========================================
// 3. TESTE DE INTEGRIDADE GOOGLE GEMINI
// ==========================================
if ($resultados['banco']['status'] == 'Operacional') {
    $apiKey = $configs['IA_API_KEY'] ?? '';
    $modelo = $configs['IA_MODEL'] ?? 'gemini-1.5-flash';
    $baseUrl = $configs['IA_BASE_URL'] ?? 'https://generativelanguage.googleapis.com/v1beta/models';

    if (empty($apiKey)) {
        $resultados['gemini'] = ['status' => 'Incompleto', 'msg' => 'API Key ausente no banco.', 'cor' => 'bg-yellow-100 text-yellow-800 border-yellow-300'];
    } else {
        // Ping no modelo exato
        $urlGemini = "$baseUrl/$modelo?key=$apiKey";
        $ch = curl_init($urlGemini);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resGemini = curl_exec($ch);
        $httpCodeGemini = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $resDecoded = json_decode($resGemini, true);

        if ($httpCodeGemini == 200) {
            $resultados['gemini'] = ['status' => 'Operacional', 'msg' => "Motor ($modelo) online e responsivo.", 'cor' => 'bg-green-100 text-green-800 border-green-300'];
        } else {
            $erro = $resDecoded['error']['message'] ?? 'Erro 404 ou 400. Verifique o nome do modelo.';
            $resultados['gemini'] = ['status' => 'Falha Crítica', 'msg' => "Motor recusou a conexão. Detalhe: " . $erro, 'cor' => 'bg-red-100 text-red-800 border-red-300'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kairós Command Center - Observabilidade</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; color: #1a202c; }
        .spinner { animation: spin 1s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="p-6 md:p-12">

    <div class="max-w-4xl mx-auto">
        
        <div class="mb-8 flex items-center justify-between">
            <div>
                <a href="index.php" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1 mb-2">
                    &larr; Voltar ao Hub Central
                </a>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Centro de Comando Kairós</h1>
                <p class="text-slate-500 mt-1">Visão de Raio-X da Infraestrutura de IA e Mensagens.</p>
            </div>
            <button onclick="window.location.reload()" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Atualizar Sensores
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- CARD: Banco de Dados -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col h-full">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-slate-100 rounded-lg">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full border <?= $resultados['banco']['cor'] ?>">
                        <?= $resultados['banco']['status'] ?>
                    </span>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 mb-1">Cofre de Dados (Hostinger)</h3>
                <p class="text-sm text-slate-500 flex-grow"><?= $resultados['banco']['msg'] ?></p>
            </div>

            <!-- CARD: WhatsApp / Meta -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col h-full">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-slate-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full border <?= $resultados['meta']['cor'] ?>">
                        <?= $resultados['meta']['status'] ?>
                    </span>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 mb-1">Ponte de Saída (WhatsApp Meta)</h3>
                <p class="text-sm text-slate-500 flex-grow"><?= $resultados['meta']['msg'] ?></p>
            </div>

            <!-- CARD: Motor de IA / Gemini -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col h-full">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-slate-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full border <?= $resultados['gemini']['cor'] ?>">
                        <?= $resultados['gemini']['status'] ?>
                    </span>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 mb-1">Cérebro Cognitivo (Google IA)</h3>
                <p class="text-sm text-slate-500 flex-grow"><?= $resultados['gemini']['msg'] ?></p>
            </div>

        </div>

        <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-100 text-blue-800 text-sm">
            <p><strong>Doutrina Kairós:</strong> Se um dos painéis acima indicar "Falha Crítica", consulte o seu <em>NotebookLM</em> procurando pela mensagem de erro exata exibida aqui para acionar o protocolo de mitigação.</p>
        </div>

    </div>

</body>
</html>