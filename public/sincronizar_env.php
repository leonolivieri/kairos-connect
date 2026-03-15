<?php
    /**
     * ARQUIVO: sincronizar_env.php
     * OBJETIVO: Varrer variáveis de ambiente e injetar chaves faltantes no Banco (Deploy).
     * STATUS: Operacional (Mão Única - Apenas Insere se não existir).
     */
    
    use src\Config\Database;
    require_once __DIR__ . '/../../kairos-connect/bootstrap.php';

    try {
        $db = Database::getInstance();

        // 1. Mapeamento de Ativos Vitais que precisamos buscar
        $chaves_esperadas = [
            'meta_phone_id'     => 'Ambiente_Meta',
            'meta_waba_id'      => 'Ambiente_Meta',
            'meta_access_token' => 'Ambiente_Meta',
            'meta_verify_token' => 'Ambiente_Meta',
            'IA_API_KEY'        => 'IA_CONFIG',
            'IA_BASE_URL'       => 'IA_CONFIG',
            'IA_MODEL'          => 'IA_CONFIG',
            'META_BASE_URL'     => 'Ambiente_Meta'
        ];

        $chaves_inseridas = 0;

        // Função recursiva para achar a chave em arrays multidimensionais do .env
        function buscarValorEnv($chave_busca, $array_env) {
            foreach ($array_env as $k => $v) {
                if (is_array($v)) {
                    $resultado = buscarValorEnv($chave_busca, $v);
                    if ($resultado !== null) return $resultado;
                } else {
                    if (strtolower($k) === strtolower($chave_busca)) return $v;
                }
            }
            return null;
        }

        // 2. Varredura e Injeção
        foreach ($chaves_esperadas as $chave => $grupo) {
            // Procura na variável superglobal
            $valor = buscarValorEnv($chave, $_ENV) ?? buscarValorEnv($chave, $_SERVER);

            if (!empty($valor)) {
                // Verifica se a chave já existe no cofre
                $stmt_check = $db->prepare("SELECT id FROM kairos_configuracoes WHERE chave = :chave");
                $stmt_check->execute([':chave' => $chave]);
                
                if (!$stmt_check->fetch()) {
                    // Se não existe, injeta com criptografia (is_secret = 1)
                    $stmt_in = $db->prepare("INSERT INTO kairos_configuracoes (chave, config_group, valor, is_secret, is_active) VALUES (:chave, :grupo, :valor, 1, 1)");
                    $stmt_in->execute([
                        ':chave' => $chave,
                        ':grupo' => $grupo,
                        ':valor' => $valor
                    ]);
                    $chaves_inseridas++;
                }
            }
        }
        
        // 3. Retorno ao Painel
        echo "<script>
            alert('Sincronização Kairós concluída.\\nForam injetadas {$chaves_inseridas} novas chaves vitais no Cofre.');
            window.location.href='admin_configs.php';
        </script>";

    } catch (Exception $e) {
        echo "<script>
            alert('Falha na Sincronização: " . addslashes($e->getMessage()) . "');
            window.location.href='admin_configs.php';
        </script>";
    }