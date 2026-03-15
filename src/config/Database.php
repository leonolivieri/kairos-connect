<?php

namespace src\Config;

use PDO;
use PDOException;

/**
 * ATIVO INTELECTUAL: Protocolo Kairós de Conexão Segura
 * CONCEITO: Singleton Híbrido com Detecção Automática de Ambiente.
 * NORMA: Uma única conexão por ciclo de execução (Eficiência de Memória).
 */
class Database {
    private static $instance = null;
    private $conn;

    /**
     * Construtor Privado: Impede instâncias externas (Regra Singleton).
     */
    private function __construct() {
        // 1. Detecção de Ambiente (Local vs Produção)
        $isLocal = $this->isLocalEnvironment();

        // 2. Extração de Credenciais baseada no carregamento do bootstrap.php
        // Priorizamos a estrutura do seu .env [Ambiente_Local] e [Ambiente_Producao]
        if ($isLocal) {
            $host = $_ENV['Ambiente_Local']['DB_HOST_LOCAL'] ?? 'localhost';
            $user = $_ENV['Ambiente_Local']['DB_USER_LOCAL'] ?? 'root';
            $pass = $_ENV['Ambiente_Local']['DB_PASS_LOCAL'] ?? '';
            $db   = $_ENV['Ambiente_Local']['DB_NAME_LOCAL'] ?? 'kairos';
        } else {
            $host = $_ENV['Ambiente_Producao']['DB_HOST_PROD'] ?? 'localhost';
            $user = $_ENV['Ambiente_Producao']['DB_USER_PROD'] ?? '';
            $pass = $_ENV['Ambiente_Producao']['DB_PASS_PROD'] ?? '';
            $db   = $_ENV['Ambiente_Producao']['DB_NAME_PROD'] ?? '';
        }

        // Validação de Integridade
        if (empty($db)) {
            die($isLocal ? "Erro: Banco de dados não definido no .env" : "Erro de Sistema: 500");
        }

        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // Segurança contra SQL Injection
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];

        try {
            $this->conn = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Em produção, nunca revelamos detalhes do servidor (Segurança Kairós)
            error_log("Erro de Conexão Kairós: " . $e->getMessage());
            die($isLocal ? "Falha na Conexão: " . $e->getMessage() : "Erro de Sistema: Contacte o suporte.");
        }
    }

    /**
     * Ponto de Entrada Único
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->conn;
    }

/**
 * Lógica de Detecção de Ambiente (Versão Calibrada Kairós)
 */
private function isLocalEnvironment() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return (stripos($host, 'localhost') !== false || stripos($host, '127.0.0.1') !== false) 
           || (php_sapi_name() === 'cli');
}
    // Travas de Segurança Inegociáveis
    private function __clone() {}
    public function __wakeup() {
        throw new \Exception("Não é permitido desserializar um Singleton.");
    }
}