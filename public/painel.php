<?php
    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';

    use src\Config\Database;

    $db = Database::getInstance();

    // Buscamos as últimas 50 mensagens para o seu painel
    $stmt = $db->query("SELECT * FROM kairos_mensagens ORDER BY created_at DESC LIMIT 50");
    $mensagens = $stmt->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>Kairós Connect - Painel</title>
        <style>
            body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
            .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
            th { background: #004a99; color: white; }
            .direcao { font-weight: bold; color: #0088cc; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Kairós Connect - Monitor de Mensagens</h1>
            <table>
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Remetente</th>
                        <th>Mensagem</th>
                        <th>Direção</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mensagens as $m): ?>
                    <tr>
                        <td><?= date('d/m H:i', strtotime($m['created_at'])) ?></td>
                        <td><?= htmlspecialchars($m['remetente_nome']) ?> (<?= $m['remetente_numero'] ?>)</td>
                        <td><?= htmlspecialchars($m['mensagem_texto']) ?></td>
                        <td class="direcao"><?= $m['direcao'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </body>
    </html>