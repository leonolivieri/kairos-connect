<?php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Classe Database - Gestão de Conexão PDO (Singleton)
 * Responsável por garantir uma única instância de conexão com o MySQL.
 */
class Database {
    private static $instance = null;
    private $conn;

    /**
     * Construtor privado para impedir instanciação externa
     */
    private function __construct() {
        // Carregamento de variáveis de ambiente (Assumindo que o .env foi processado)
        $host = getenv('DB_HOST') ?: 'localhost';
        $db   = getenv('DB_NAME') ?: 'KAIROS';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: 'Kairos@Admin';
        $port = getenv('DB_PORT') ?: '3306';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Em produção, este erro deve ser registado num log, não exibido
            die("Erro de Conexão à Base de Dados: " . $e->getMessage());
        }
    }

    /**
     * Retorna a instância única da conexão
     * @return PDO
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->conn;
    }

    /**
     * Impede a clonagem da instância
     */
    private function __clone() {}

    /**
     * Impede a desserialização da instância
     */
    public function __wakeup() {}
}