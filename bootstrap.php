<?php
    /**
     * ARQUIVO: bootstrap.php
     * OBJETIVO: Inicializar ambiente, Autoloader e Injetar variáveis do Cofre (.env).
    */

    if (!defined('BOOTSTRAP_LOADED')) {
        define('BOOTSTRAP_LOADED', true);
    date_default_timezone_set('America/Sao_Paulo');

    // --- INJEÇÃO DE AMBIENTE (Cofre) [cite: 2026-03-09] ---

    $envPath = __DIR__ . '/.env';

    if (file_exists($envPath)) {
        // Carrega processando as seções []
        $envRaw = parse_ini_file($envPath, true);
        
        foreach ($envRaw as $section => $values) {
            if (is_array($values)) {
                foreach ($values as $key => $value) {
                    // Limpeza de aspas e lixo invisível
                    $cleanValue = trim($value, " \t\n\r\0\x0B\"'");
                    $_ENV[$section][$key] = $cleanValue;
                }
            } else {
                $_ENV[$section] = trim($values, " \t\n\r\0\x0B\"'");
            }
        }
    }
    // --- AUTOLOADER KAÍROS [cite: 2026-03-08] ---
    spl_autoload_register(function ($class) {
        $base_dir = __DIR__ . '/src/';
        $class = str_replace('src\\', '', $class);
        $file = $base_dir . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    });
   }