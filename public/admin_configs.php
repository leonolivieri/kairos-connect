<?php 
    /**
     * ARQUIVO: public/admin_configs.php
     * OBJETIVO: Interface de Gestão de Ativos e Parâmetros do Sistema.
     * STATUS: Operacional - Correção de Escapamento JS para Prompts Longos.
     */
    
    use src\Config\Database;
    use src\Helpers\SecurityHelper;

    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';

    $alerta_drift = false;
    $chaves_faltantes = [];

    try {
        $db = Database::getInstance();
        $status_conexao = "✅ CONEXÃO ESTABELECIDA COM SUCESSO";
        
        $ativos_vitais = ['META_PHONE_ID', 'META_WABA_ID', 'META_ACCESS_TOKEN', 'META_VERIFY_TOKEN', 'IA_API_KEY'];
        
        $stmt_check = $db->query("SELECT chave FROM kairos_configuracoes");
        $chaves_banco = $stmt_check->fetchAll(PDO::FETCH_COLUMN);
        
        $chaves_banco_upper = array_map('strtoupper', $chaves_banco);

        foreach ($ativos_vitais as $vital) {
            if (!in_array(strtoupper($vital), $chaves_banco_upper)) {
                $chaves_faltantes[] = $vital; 
            }
        }

        if (count($chaves_faltantes) > 0) {
            $alerta_drift = true;
        }

        $stmt = $db->query("SELECT * FROM kairos_configuracoes ORDER BY config_group, chave ASC");
        $configuracoes = $stmt->fetchAll();
        
    } catch (Exception $e) {
        $status_conexao = "❌ FALHA: " . $e->getMessage();
        $configuracoes = []; 
    }

    $dsn_conferencia = $_ENV['Ambiente_Producao']['DB_NAME_PROD'] ?? 'Local';
    $user_debug = $_ENV['Ambiente_Producao']['DB_USER_PROD'] ?? 'Root';
?>

<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/style.css?v=1.3">
        <title>Kairós Connect - Gestão de Ativos</title>
        <style>
            .alert-banner { background-color: #fee2e2; border-left: 6px solid #ef4444; color: #991b1b; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; font-size: 0.95rem; margin-bottom: 20px; border-radius: 0 4px 4px 0;}
            .alert-banner strong { color: #7f1d1d; display: block; margin-bottom: 5px;}
            .alert-banner ul { margin: 5px 0 0 20px; padding: 0; }
            .btn-sync { background-color: #ef4444; color: white; border: none; padding: 8px 15px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 0.85rem;}
            .btn-sync:hover { background-color: #dc2626; }
            .btn-back { display: inline-block; margin-bottom: 15px; color: #8bb1c4; text-decoration: none; font-size: 0.9rem; font-weight: bold; transition: color 0.2s; }
            .btn-back:hover { color: #1a5f7a; }
            .table-wrapper { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
            td.actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
            td.actions form { margin: 0; }
            td.actions button { margin: 0; }

            @media (max-width: 768px) {
                .container { padding: 20px 15px; margin: 10px; width: auto; }
                header { flex-direction: column; align-items: flex-start; gap: 15px; }
                .btn-add { width: 100%; text-align: center; padding: 12px; }
                .connection-panel { position: static; width: auto; margin: 10px 10px 20px 10px; text-align: left; }
                .alert-banner { flex-direction: column; align-items: flex-start; gap: 15px; }
                .btn-sync { width: 100%; padding: 12px; }
                .modal-content { width: 95%; padding: 20px; margin: 20px; }
                .modal-actions { flex-direction: column; gap: 10px; }
                .btn-cancel, .btn-submit { width: 100%; }
                table { min-width: 800px; }
            }
        </style>
    </head>
    <body>

        <div class="connection-panel">
            <h3>CONFERÊNCIA DE CONEXÃO</h3>
            <p><strong>DSN:</strong> <?php echo $dsn_conferencia; ?></p>
            <p><strong>USER:</strong> <?php echo $user_debug; ?></p>
            <p class="status"><?php echo $status_conexao; ?></p>
        </div>

        <div class="container">
            <a href="index.php" class="btn-back">⬅ Voltar ao Hub Central</a>
            
            <header>
                <h1>KAIRÓS CONNECT > GESTÃO DE ATIVOS</h1>
                <button class="btn-add" onclick="abrirModalNovo()">+ Novo Parâmetro</button>
            </header>

            <?php if ($alerta_drift): ?>
            <div class="alert-banner">
                <div>
                    <strong>⚠️ ALERTA: Desvio de Infraestrutura</strong>
                    Ativos vitais estão faltando no banco e a operação pode falhar:
                    <ul>
                        <?php foreach ($chaves_faltantes as $falta): ?>
                            <li><code><?php echo $falta; ?></code></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <form action="sincronizar_env.php" method="POST" style="margin: 0;">
                    <button type="submit" class="btn-sync" onclick="return confirm('Deseja sincronizar as chaves vitais?');">Sincronizar .env</button>
                </form>
            </div>
            <?php endif; ?>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Chave</th>
                            <th>Grupo</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($configuracoes)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-dim);">
                                    Nenhum parâmetro encontrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($configuracoes as $conf): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($conf['chave']) ?></strong></td>
                                    <td><span class="badge"><?= htmlspecialchars($conf['config_group']) ?></span></td>
                                    <td>
                                        <?php 
                                            if ($conf['is_secret'] == 1) {
                                                echo "<span style='color: var(--text-dim);'>••••••••••••••••</span>";
                                            } else {
                                                $valor_exibicao = htmlspecialchars($conf['valor']);
                                                if (strlen($valor_exibicao) > 30) {
                                                    $valor_curto = substr($valor_exibicao, 0, 30) . "...";
                                                    echo "<span title='{$valor_exibicao}' style='cursor: help; border-bottom: 1px dotted #ccc;'>{$valor_curto}</span>";
                                                } else {
                                                    echo $valor_exibicao;
                                                }
                                            }
                                        ?>
                                    </td>
                                    <td class="<?= $conf['is_active'] == 1 ? 'is_active' : '' ?>">
                                        ● <?= $conf['is_active'] == 1 ? 'Ativo' : 'Inativo' ?>
                                    </td>
                                    <td class="actions">
                                        <?php 
                                            // BLINDAGEM: Usamos json_encode para escapar aspas e quebras de linha com segurança para o JavaScript
                                            $valParaJS = $conf['is_secret'] == 1 ? "" : $conf['valor'];
                                        ?>
                                        <button type="button" onclick='editarModal(<?= json_encode($conf["chave"]) ?>, <?= json_encode($conf["config_group"]) ?>, <?= json_encode($valParaJS) ?>, <?= $conf["is_secret"] ?>)'>Editar</button>
                                        
                                        <form action="excluir_config.php" method="POST" onsubmit="return confirm('ALERTA KAIRÓS:\nTem certeza que deseja excluir este ativo definitivamente?');">
                                            <input type="hidden" name="id" value="<?= $conf['id'] ?>">
                                            <button type="submit" class="btn-delete">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div> 
        </div>

        <!-- Módulo Modal -->
        <div id="modalParametro" class="modal-overlay">
            <div class="modal-content">
                <h2 id="modalTitulo">+ Adicionar Ativo</h2>
                <form id="formParametro" action="processa_config.php" method="POST">
                    <div class="form-group">
                        <label class="form-label">GRUPO</label>
                        <select name="config_group" id="modalGrupo" class="form-control">
                            <option value="IA_CONFIG">IA_CONFIG</option>
                            <option value="Ambiente_Meta">Ambiente_Meta</option>
                            <option value="EXPEDIENTE">EXPEDIENTE</option>
                            <option value="SISTEMA">SISTEMA</option>
                            <option value="IA_PROMPTS">IA_PROMPTS</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">CHAVE (Identificador Único)</label>
                        <input type="text" name="chave" id="modalChave" class="form-control" placeholder="Ex: GOOGLE_API_KEY" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">VALOR</label>
                        <textarea name="valor" id="modalValor" rows="6" class="form-control" required></textarea>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_secret" value="1" id="is_secret">
                        <label for="is_secret">🔒 Criptografar como dado sensível</label>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="fecharModal()">Cancelar</button>
                        <button type="submit" class="btn-submit">Gravar Ativo</button>
                    </div>
                </form>
            </div>
        </div>

        <script src="js/script.js"></script>
    </body>
</html>