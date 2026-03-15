<?php
    /**
     * ARQUIVO: processa_config.php
     * OBJETIVO: Sincronizar dados do Modal com a estrutura física kairos_configuracoes.
     * STATUS: Corrigido (is_active agora é inteiro 1).
    */

    use src\Config\Database;
    use src\Helpers\SecurityHelper;
    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';
   
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die("Acesso direto não permitido.");
    }

    try {
        $db = Database::getInstance();

        $config_group = trim($_POST['config_group'] ?? 'Sistema');
        $chave        = trim($_POST['chave'] ?? '');
        $valor        = $_POST['valor'] ?? '';
        $is_secret    = (bool)($_POST['is_secret'] ?? false);
        
        // CORREÇÃO CRÍTICA AQUI: O banco espera um número (1), não a palavra 'Ativo'
        $is_active    = 1; 
        
        if (empty($chave) || empty($valor)) {
            throw new Exception("Os campos Chave e Valor são mandatórios.");
        }

        $valor_final = $is_secret ? SecurityHelper::encrypt($valor) : $valor;

        $sql = "INSERT INTO kairos_configuracoes (chave, config_group, valor, is_secret, is_active) 
                VALUES (:chave, :grupo, :valor, :is_secret, :is_active)
                ON DUPLICATE KEY UPDATE 
                valor = VALUES(valor), 
                config_group = VALUES(config_group), 
                is_secret = VALUES(is_secret)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':chave'     => $chave,
            ':grupo'     => $config_group,
            ':valor'     => $valor_final,
            ':is_secret' => (int)$is_secret,
            ':is_active' => $is_active 
        ]);

        header("Location: admin_configs.php?sucesso=1");
        exit;

    } catch (Exception $e) {
        error_log("Erro Crítico Kairós: " . $e->getMessage());
        header("Location: admin_configs.php?erro=" . urlencode($e->getMessage()));
        exit;
    }