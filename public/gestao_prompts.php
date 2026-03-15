<?php
/**
 * ARQUIVO: public/gestao_prompts.php
 * OBJETIVO: Centro de Controlo Cognitivo - Gestão de Personalidade da IA.
 * STATUS: Fase 3 - Visão Gerencial (Recuperação e Alinhamento SoC).
 * VERSÃO: 2.0 (Integração Total com Arquitetura CSS/JS Mestre)
 * DATA DE CRIAÇÃO: 14 de Março de 2026
 * ÚLTIMA ALTERAÇÃO: 14 de Março de 2026, 19:25 (BRT)
 * AUTOR: Engenharia Kairós (Leon)
 */

require_once __DIR__ . '/../../kairos-connect/bootstrap.php';
use src\Config\Database;

// ==========================================
// 1. MOTOR BACKEND (Leitura dos Prompts)
// ==========================================
$prompts = [];
$erro_db = '';

try {
    $db = Database::getInstance();
    // Busca todos os ativos que tenham 'PROMPT' no nome da chave
    $stmt = $db->query("SELECT chave, valor FROM kairos_configuracoes WHERE chave LIKE 'PROMPT_%'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $prompts[] = $row;
    }
} catch (Exception $e) {
    $erro_db = "Falha ao aceder ao cofre de dados: " . $e->getMessage();
}

// (Opcional) Lógica de POST para Salvar/Editar pode ser acoplada aqui futuramente
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca de Prompts - Kairós Connect</title>
    
    <!-- A magia da arquitetura: O design já está todo aqui -->
    <link rel="stylesheet" href="css/style.css?v=3.0">
</head>
<body>

    <div class="container">
        
        <header class="hub-header">
            <h1>🧠 CENTRO COGNITIVO</h1>
            <p>Gestão de Comportamento e Doutrina da Inteligência Artificial</p>
        </header>

        <div class="connection-panel" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3>Módulo de Personalidade (System Prompts)</h3>
                <p>Ajuste as diretrizes de contexto que a IA usará ao interagir com o Bitrix24 e o WhatsApp.</p>
                <?php if($erro_db): ?>
                    <p style="color: var(--danger); font-weight: bold;"><?= $erro_db ?></p>
                <?php endif; ?>
            </div>
            <div>
                <a href="index.php" class="btn btn-edit" style="margin-right: 10px;">&larr; Voltar ao Hub</a>
                <button class="btn btn-primary" onclick="abrirModalNovo()">+ Novo Prompt</button>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">Identificador (Chave)</th>
                    <th style="width: 60%;">Instrução Sistêmica (Resumo)</th>
                    <th style="width: 15%; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($prompts)): ?>
                <tr>
                    <td colspan="3" style="text-align: center; color: var(--text-dim); padding: 30px;">
                        Nenhum prompt cognitivo carregado. O banco está vazio ou as chaves não possuem o prefixo 'PROMPT_'.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($prompts as $p): ?>
                    <tr>
                        <td style="font-family: monospace; color: var(--accent);">
                            <strong><?= htmlspecialchars($p['chave']) ?></strong>
                        </td>
                        <td>
                            <div style="color: var(--text-dim); font-size: 0.85rem; max-height: 40px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?= htmlspecialchars($p['valor']) ?>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <!-- Usa a função nativa do nosso script.js para abrir a edição -->
                            <button class="btn btn-edit" onclick="editarModal('<?= htmlspecialchars($p['chave']) ?>', 'PROMPT', '<?= htmlspecialchars(addslashes($p['valor'])) ?>', 0)">Afinar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

    <!-- MODAL DE EDIÇÃO / CRIAÇÃO (Herdado do CSS Mestre) -->
    <div id="modalParametro" class="modal-overlay">
        <div class="modal-content">
            <h2>+ Configurar Doutrina IA</h2>
            <form id="formParametro" method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Identificador (Ex: PROMPT_VENDAS, PROMPT_SUPORTE)</label>
                    <input type="text" name="chave" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Regras de Comportamento (O que a IA deve ser/fazer)</label>
                    <textarea name="valor" class="form-control" style="min-height: 150px;" required placeholder="Ex: Você é um assistente sênior da Kairós. Seu tom de voz deve ser executivo e resolutivo..."></textarea>
                </div>
                
                <!-- Campos ocultos para manter compatibilidade com o sistema de base de dados -->
                <input type="hidden" name="config_group" value="PROMPT">
                <input type="hidden" name="is_secret" value="0">
                
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px;">
                    <button type="button" class="btn btn-edit" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary" style="background-color: var(--success);">Atualizar Cérebro</button>
                </div>
            </form>
        </div>
    </div>

    <!-- O motor de JavaScript que fará o modal funcionar -->
    <script src="js/script.js?v=2.1"></script>
</body>
</html>