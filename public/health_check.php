<?php
/**
 * ARQUIVO: public/health_check.php
 * OBJETIVO: Centro de Comando Kairós - Painel Assíncrono de Alta Performance.
 * STATUS: Fase 3 - Visão Gerencial.
 * VERSÃO: 5.1 (Dry-Run Post Meta Bypass)
 * DATA DE CRIAÇÃO: 14 de Março de 2026
 * ÚLTIMA ALTERAÇÃO: 14 de Março de 2026, 14:05 (BRT)
 * AUTOR: Engenharia Kairós (Leon)
 */

use src\Config\Database;
require_once __DIR__ . '/../../kairos-connect/bootstrap.php';

// =========================================================================
// MOTOR BACKEND (Só executa se for chamado pelo JavaScript via URL ?action=...)
// =========================================================================
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $configs = [];
    
    // 1. Tentativa Rápida de Carregar Configurações (Base para todos os testes)
    try {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT chave, valor FROM kairos_configuracoes");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $configs[$row['chave']] = $row['valor']; }
    } catch (Exception $e) {
        if ($_GET['action'] === 'banco') {
            echo json_encode(['status' => 'Falha Crítica', 'msg' => 'Sem conexão: ' . $e->getMessage(), 'cor' => 'bg-red-100 text-red-800 border-red-300']); exit;
        }
        echo json_encode(['status' => 'Pendente', 'msg' => 'Aguardando Banco de Dados...', 'cor' => 'bg-yellow-100 text-yellow-800 border-yellow-300']); exit;
    }

    // AÇÃO A: Testar Banco
    if ($_GET['action'] === 'banco') {
        echo json_encode(['status' => 'Operacional', 'msg' => 'Conexão estável e ativos carregados.', 'cor' => 'bg-green-100 text-green-800 border-green-300']); exit;
    }

    // AÇÃO B: Testar Meta (WhatsApp) - TÁTICA DRY-RUN (Simulação de Envio Vazio)
    if ($_GET['action'] === 'meta') {
        $metaToken = $configs['META_ACCESS_TOKEN'] ?? '';
        $phoneId = $configs['META_PHONE_ID'] ?? '';
        
        if (empty($metaToken) || empty($phoneId)) {
            echo json_encode(['status' => 'Incompleto', 'msg' => 'Token ou Phone ID ausentes.', 'cor' => 'bg-yellow-100 text-yellow-800 border-yellow-300']); exit;
        }
        
        // Fazemos um POST vazio para /messages. 
        $urlMeta = "https://graph.facebook.com/v18.0/$phoneId/messages";
        $ch = curl_init($urlMeta);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([])); // Payload intencionalmente vazio
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $metaToken",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
        $resMeta = curl_exec($ch);
        $httpCodeMeta = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $resDecoded = json_decode($resMeta, true);
        
        // Doutrina Kairós: Se devolver 400 (Bad Request de sintaxe), significa que o Token PASSOU na segurança (não deu Erro 401/190).
        if ($httpCodeMeta == 400 || $httpCodeMeta == 200) {
            echo json_encode(['status' => 'Operacional', 'msg' => 'Token autenticado (Permissão de envio confirmada).', 'cor' => 'bg-green-100 text-green-800 border-green-300']);
        } else {
            $erro = $resDecoded['error']['message'] ?? 'Token Expirado ou Inválido.';
            echo json_encode(['status' => 'Falha Crítica', 'msg' => $erro, 'cor' => 'bg-red-100 text-red-800 border-red-300']);
        }
        exit;
    }

    // AÇÃO C: Testar Gemini (Google IA)
    if ($_GET['action'] === 'gemini') {
        $apiKey = $configs['IA_API_KEY'] ?? '';
        $modelo = $configs['IA_MODEL'] ?? 'gemini-2.5-flash';
        $baseUrl = $configs['IA_BASE_URL'] ?? 'https://generativelanguage.googleapis.com/v1beta/models';

        if (empty($apiKey)) {
            echo json_encode(['status' => 'Incompleto', 'msg' => 'API Key ausente.', 'cor' => 'bg-yellow-100 text-yellow-800 border-yellow-300']); exit;
        }

        $ch = curl_init("$baseUrl/$modelo?key=$apiKey");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
        $resGemini = curl_exec($ch);
        $httpCodeGemini = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $resDecoded = json_decode($resGemini, true);
        if ($httpCodeGemini == 200) {
            echo json_encode(['status' => 'Operacional', 'msg' => "Motor ($modelo) online.", 'cor' => 'bg-green-100 text-green-800 border-green-300']);
        } else {
            $erro = $resDecoded['error']['message'] ?? 'Motor inacessível. Verifique API Key.';
            echo json_encode(['status' => 'Falha Crítica', 'msg' => $erro, 'cor' => 'bg-red-100 text-red-800 border-red-300']);
        }
        exit;
    }
}
// =========================================================================
// FRONTEND VISUAL (Renderiza em 0.1s e aciona o Backend via JavaScript)
// =========================================================================
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
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; }
        .spin { animation: spin 1s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="p-6 md:p-12">

    <div class="max-w-4xl mx-auto">
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="index.php" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1 mb-2 transition-colors">
                    &larr; Voltar ao Hub Central
                </a>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Raio-X Kairós</h1>
                <p class="text-slate-500 mt-1">Diagnóstico profundo (Assíncrono) da infraestrutura.</p>
            </div>
            <button onclick="iniciarTestes()" class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition flex items-center justify-center gap-2 shadow-md hover:shadow-lg w-full sm:w-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Rodar Testes Novamente
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- CARD: Banco -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col h-full hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    </div>
                    <span id="badge-banco" class="px-3 py-1 text-xs font-bold rounded-full border bg-gray-100 text-gray-600 border-gray-200">
                        Testando...
                    </span>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Cofre de Dados</h3>
                <p id="msg-banco" class="text-sm text-slate-500 flex-grow">Aguardando resposta do servidor...</p>
            </div>

            <!-- CARD: Meta -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col h-full hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-green-50 rounded-lg border border-green-100">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <span id="badge-meta" class="px-3 py-1 text-xs font-bold rounded-full border bg-gray-100 text-gray-600 border-gray-200">
                        Testando...
                    </span>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Ponte de Saída (Meta)</h3>
                <p id="msg-meta" class="text-sm text-slate-500 flex-grow">Aguardando API do WhatsApp...</p>
            </div>

            <!-- CARD: Gemini -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col h-full hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-blue-50 rounded-lg border border-blue-100">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <span id="badge-gemini" class="px-3 py-1 text-xs font-bold rounded-full border bg-gray-100 text-gray-600 border-gray-200">
                        Testando...
                    </span>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Motor Google IA</h3>
                <p id="msg-gemini" class="text-sm text-slate-500 flex-grow">Aguardando resposta do motor...</p>
            </div>

        </div>
    </div>

    <!-- SCRIPT DE REQUISIÇÃO PARALELA -->
    <script>
        function resetarCard(id) {
            document.getElementById('badge-' + id).className = "px-3 py-1 text-xs font-bold rounded-full border bg-blue-50 text-blue-600 border-blue-200 spin";
            document.getElementById('badge-' + id).innerText = "⏳ Ping...";
            document.getElementById('msg-' + id).innerText = "Analisando rota...";
        }

        function aplicarResultado(id, data) {
            document.getElementById('badge-' + id).className = "px-3 py-1 text-xs font-bold rounded-full border " + data.cor;
            document.getElementById('badge-' + id).innerText = data.status;
            document.getElementById('msg-' + id).innerText = data.msg;
        }

        async function testarServico(id) {
            resetarCard(id);
            try {
                const response = await fetch('health_check.php?action=' + id);
                const data = await response.json();
                aplicarResultado(id, data);
            } catch (error) {
                aplicarResultado(id, {status: 'Erro Local', msg: 'Falha de rede. Verifique logs.', cor: 'bg-red-100 text-red-800 border-red-300'});
            }
        }

        function iniciarTestes() {
            testarServico('banco');
            testarServico('meta');
            testarServico('gemini');
        }

        window.onload = iniciarTestes;
    </script>
</body>
</html>