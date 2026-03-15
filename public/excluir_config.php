<?php
    /**
     * ARQUIVO: excluir_config.php
     * OBJETIVO: Remover definitivamente um ativo/parâmetro da base de dados.
     * STATUS: Homologado com proteção POST.
    */

    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';
    use src\Config\Database;

    // 1. Blindagem de Rota (Apenas aceita POST)
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die("Acesso direto não permitido. Utilize a interface do sistema.");
    }

    try {
        $db = Database::getInstance();

        // 2. Captura e Validação Estrita do ID
        // Forçamos a conversão para inteiro para evitar Injeção de SQL.
        $id = (int)($_POST['id'] ?? 0);

        if ($id === 0) {
            throw new Exception("ID inválido ou não fornecido para exclusão.");
        }

        // 3. Execução da Deleção
        $sql = "DELETE FROM kairos_configuracoes WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);

        // Redireciona de volta com flag de sucesso
        header("Location: admin_configs.php?sucesso=excluido");
        exit;

    } catch (Exception $e) {
        error_log("Erro Crítico Kairós (Exclusão): " . $e->getMessage());
        header("Location: admin_configs.php?erro=" . urlencode("Falha ao excluir: " . $e->getMessage()));
        exit;
    }