<?php
    /**
     * ARQUIVO: painel.php
     * OBJETIVO: Monitoramento em tempo real dos webhooks (Logs do WhatsApp).
     * STATUS: Operacional com Blindagem Kairós (Fase 2).
     */

    use src\Config\Database;
    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';

    $mensagens = [];
    $erro_infra = false;
    $mensagem_erro = "";

    try {
        $db = Database::getInstance();
        
        // Tentativa de leitura. Se a tabela não existir, o Catch assume o controle.
        $stmt = $db->query("SELECT * FROM kairos_mensagens ORDER BY created_at DESC LIMIT 50");
        $mensagens = $stmt->fetchAll();
        
    } catch (Exception $e) {
        $erro_infra = true;
        $mensagem_erro = $e->getMessage();
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kairós Connect - Monitor de Logs</title>
    <!-- Herdando o design oficial do ecossistema -->
    <link rel="stylesheet" href="css/style.css?v=1.2">
    <style>
        .btn-back { display: inline-block; margin-bottom: 15px; color: #8bb1c4; text-decoration: none; font-size: 0.9rem; font-weight: bold; transition: color 0.2s; }
        .btn-back:hover { color: #1a5f7a; }
        
        .table-wrapper { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        
        .alert-banner { background-color: #fee2e2; border-left: 6px solid #ef4444; color: #991b1b; padding: 15px 20px; font-size: 0.95rem; margin-bottom: 20px; border-radius: 0 4px 4px 0;}
        .alert-banner strong { color: #7f1d1d; display: block; margin-bottom: 5px;}
        
        /* Estilos específicos para o log */
        .msg-text { font-family: monospace; background: #f8f9fa; color: #1e293b; border: 1px solid #cbd5e1; padding: 5px 8px; border-radius: 4px; display: inline-block; word-break: break-all; max-width: 400px; }
        .dir-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
        .dir-in { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .dir-out { background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }

        @media (max-width: 768px) {
            .container { padding: 20px 15px; margin: 10px; width: auto; }
            header { flex-direction: column; align-items: flex-start; gap: 15px; }
        }
    </style>
</head>
<body>

    <div class="container">
        <a href="index.php" class="btn-back">⬅ Voltar ao Hub Central</a>
        
        <header>
            <h1>KAIRÓS CONNECT > LOGS DO WHATSAPP</h1>
            <!-- Espaço reservado para futuros filtros (Data, Número, etc) -->
        </header>

        <?php if ($erro_infra): ?>
        <div class="alert-banner">
            <strong>⚠️ ALERTA DE INFRAESTRUTURA</strong>
            A tabela de Logs (kairos_mensagens) ainda não foi detectada no banco de dados ou houve uma falha de conexão.<br>
            <small>Detalhe técnico: <?php echo htmlspecialchars($mensagem_erro); ?></small>
        </div>
        <?php endif; ?>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 150px;">Data/Hora</th>
                        <th>Remetente</th>
                        <th>Mensagem</th>
                        <th style="width: 100px;">Direção</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($mensagens) && !$erro_infra): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-dim); padding: 30px;">
                                Nenhum tráfego registrado. O Webhook está a aguardar mensagens.
                            </td>
                        </tr>
                    <?php elseif (!$erro_infra): ?>
                        <?php foreach ($mensagens as $m): ?>
                            <tr>
                                <td style="color: #6c757d; font-size: 0.85rem;">
                                    <?= date('d/m/Y H:i:s', strtotime($m['created_at'])) ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($m['remetente_nome']) ?></strong><br>
                                    <small style="color: #8bb1c4;"><?= htmlspecialchars($m['remetente_numero']) ?></small>
                                </td>
                                <td>
                                    <span class="msg-text"><?= htmlspecialchars($m['mensagem_texto']) ?></span>
                                </td>
                                <td>
                                    <?php 
                                        // Estética condicional para a direção
                                        $dir = strtoupper(htmlspecialchars($m['direcao']));
                                        $classe_dir = ($dir === 'IN' || $dir === 'RECEBIDA') ? 'dir-in' : 'dir-out';
                                    ?>
                                    <span class="dir-badge <?= $classe_dir ?>"><?= $dir ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>