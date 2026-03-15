<?php

    namespace src\Config;

    use PDO;
    use PDOException;
    /**
     * NOME: Database
     * CONCEITO: Singleton de Conexão com o Banco de Dados.
     * PORQUE O NOME: "Database" é autoexplicativo e reflete seu papel central na gestão de conexões.
     * OBJETIVO: Fornecer uma conexão segura, eficiente e fácil de usar para toda a aplicação.
     * DIFERENCIAL: Detecção automática de ambiente (local vs produção) e carregamento inteligente do .env.
     */

    // Trava de Segurança contra redeclaração [cite: 2026-03-10]

    if (!class_exists('src\Config\Database')) {
        class Database {
            private static $instance = null;
            private $conn;

            private function __construct() {
                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $db   = $_ENV['DB_NAME'] ?? '';
                $user = $_ENV['DB_USER'] ?? '';
                $pass = $_ENV['DB_PASS'] ?? '';

                try {
                    $this->conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    die("Erro na conexão com o Banco de Dados: " . $e->getMessage());
                }
            }

            public static function getInstance() {
                if (self::$instance === null) {
                    self::$instance = new Database();
                }
                return self::$instance->conn;
            }
        }
    }


    class Database {
        private static $instance = null;
        private $conn;

        /**
         * Construtor privado: Aqui injetamos a inteligência híbrida Kairós
         */
        private function __construct() {
            // 1. Carrega o .env (Independente de onde o script é chamado)
            $envPath = __DIR__ . '/../../.env';
            if (!file_exists($envPath)) {
                die("Erro Crítico: Arquivo .env não encontrado em $envPath");
            }
            $env = parse_ini_file($envPath, true);

            // 2. Detecção de Ambiente Híbrida
            $isLocal = (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == 'localhost:8080')) || (php_sapi_name() === 'cli');

            if ($isLocal) {
                $host = $env['Ambiente_Local']['DB_HOST_LOCAL'] ?? 'localhost';
                // Ajustado para as chaves do seu .env atual
                $user = $env['Ambiente_Local']['DB_USER_LOCAL'] ?? 'root';
                $pass = $env['Ambiente_Local']['DB_PASS_LOCAL'] ?? '';
                $db   = $env['Ambiente_Local']['DB_NAME_LOCAL'] ?? 'kairos';
            } else {
                // Ajustado para as chaves da produção no seu .env
                $host = $env['Ambiente_Producao']['DB_HOST_PROD'] ?? 'localhost';
                $user = $env['Ambiente_Producao']['DB_USER_PROD'] ?? '';
                $pass = $env['Ambiente_Producao']['DB_PASS_PROD'] ?? '';
                $db   = $env['Ambiente_Producao']['DB_NAME_PROD'] ?? '';        }

            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
            
            // 3. Opções de Segurança Profissional (O que eu tinha descartado)
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                $this->conn = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Em local mostramos tudo, em produção escondemos o erro técnico
                die($isLocal ? "Erro de Conexão: " . $e->getMessage() : "Erro de Sistema: Contacte o suporte.");
            }
        }

        /**
         * Ponto de entrada único (Singleton)
         */
        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance->conn;
        }

        // Travas de Segurança Inegociáveis

        /**
         * Impede a clonagem da instância
         */
        private function __clone() {}

        /**
         * Impede a desserialização da instância
         */
        public function __wakeup() {}
    }