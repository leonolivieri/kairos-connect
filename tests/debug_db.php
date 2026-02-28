<?php
/**
 * ARQUIVO: tests/debug_db.php
 * OBJETIVO: Identificar a credencial correta do seu MySQL Local.
 */

echo "=== KAIRÓS DIAGNOSTIC: SCANNER DE CONEXÃO LOCAL ===\n\n";

$scenarios = [
    ['user' => 'root', 'pass' => 'Kairos@Admin', 'desc' => 'Senha Documentada (v2.3)'],
    ['user' => 'root', 'pass' => '',              'desc' => 'Sem Senha (Padrão Installer)'],
    ['user' => 'root', 'pass' => 'root',          'desc' => 'Senha Root (Padrão Comum)']
];

foreach ($scenarios as $s) {
    echo "A testar cenário: {$s['desc']}... ";
    try {
        $dsn = "mysql:host=localhost;charset=utf8mb4";
        $conn = new PDO($dsn, $s['user'], $s['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        echo "✅ SUCESSO!\n";
        echo "--------------------------------------------------\n";
        echo "USE ESTA CONFIGURAÇÃO NO SEU Database.php:\n";
        echo "Utilizador: {$s['user']}\n";
        echo "Senha: '{$s['pass']}'\n";
        
        // Check se a base de dados KAIROS existe
        $checkDB = $conn->query("SHOW DATABASES LIKE 'KAIROS'")->fetch();
        if ($checkDB) {
            echo "Base de Dados 'KAIROS': ✅ EXISTE\n";
        } else {
            echo "Base de Dados 'KAIROS': ❌ NÃO EXISTE (Precisa de a criar no phpMyAdmin local)\n";
        }
        echo "--------------------------------------------------\n";
        exit;
    } catch (PDOException $e) {
        echo "❌ FALHA (" . $e->getCode() . ")\n";
    }
}

echo "\n🚨 NENHUM CENÁRIO FUNCIONOU.\n";
echo "DICA: Verifique se o serviço 'MySQL' ou 'MariaDB' está ATIVO no seu Windows.";