<?php

namespace src\Helpers;

/**
 * Classe SecurityHelper - Criptografia de Elite (AES-256-CBC)
 * Responsável por blindar dados sensíveis antes de chegarem ao Banco de Dados.
 */
class SecurityHelper {
    
    // Método de cifragem padrão
    private static $method = 'aes-256-cbc';

    /**
     * Encripta um valor de texto plano
     * @param string $value Texto original
     * @return string Texto cifrado em Base64
     */
    public static function encrypt($value) {
        $key = self::getKey();
        // Gerar um IV (Vetor de Inicialização) aleatório para cada criptografia
        $iv_length = openssl_cipher_iv_length(self::$method);
        $iv = openssl_random_pseudo_bytes($iv_length);
        
        $encrypted = openssl_encrypt($value, self::$method, $key, 0, $iv);
        
        // Retornar IV + Texto Cifrado para permitir a decifração posterior
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decifra um valor vindo do banco de dados
     * @param string $value Texto em Base64
     * @return string|bool Texto original ou false em caso de falha
     */

    
    public static function decrypt($value) {
        $key = self::getKey();
        $data = base64_decode($value, true);
        if ($data === false) {
            // Se a decodificação falhar, retornamos false
            return false;
        }
        $iv_length = openssl_cipher_iv_length(self::$method);
        
        if (strlen($data) <= $iv_length) {
            // Se o dado for menor que o IV, é um formato inválido
            return false;
        }
        $iv = substr($data, 0, $iv_length);
        $encrypted = substr($data, $iv_length);
        $decrypted = openssl_decrypt($encrypted, self::$method, $key, 0, $iv);

        return $decrypted !== false ? $decrypted : $value;
    }

    /**
     * Obtém a chave mestra do ambiente
     */
    private static function getKey() {
        // Tenta pegar do ambiente, se não houver, usa um fallback de emergência
        $key = getenv('MASTER_KEY') ?: 'K4ir05_V3ntur3s_Fallback_Key_2026';
        // A chave AES-256 precisa ter exatamente 32 caracteres
        return substr(hash('sha256', $key), 0, 32);
    }
}