<?php
/**
 * JWT Token Yardımcı Sınıfı
 * Emlak-Delfino Projesi
 */

class JWT {
    private static $secret_key = 'emlak_delfino_secret_key_2024'; // Gerçek projede env'den alınmalı
    private static $algorithm = 'HS256';
    
    /**
     * JWT token oluşturur
     */
    public static function encode($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$algorithm]);
        $payload = json_encode($payload);
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, self::$secret_key, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    /**
     * JWT token'ı doğrular ve decode eder
     */
    public static function decode($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
        $signature = $parts[2];
        
        // Signature doğrulama
        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], 
            base64_encode(hash_hmac('sha256', $parts[0] . "." . $parts[1], self::$secret_key, true)));
        
        if ($signature !== $expectedSignature) {
            return false;
        }
        
        $payloadData = json_decode($payload, true);
        
        // Token süresi kontrolü
        if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
            return false;
        }
        
        return $payloadData;
    }
    
    /**
     * Token'dan kullanıcı ID'sini alır
     */
    public static function getUserId($token) {
        $payload = self::decode($token);
        return $payload ? $payload['user_id'] : false;
    }
    
    /**
     * Token'dan kullanıcı rolünü alır
     */
    public static function getUserRole($token) {
        $payload = self::decode($token);
        return $payload ? $payload['role_id'] : false;
    }
}
?> 