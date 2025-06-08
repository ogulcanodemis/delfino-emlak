<?php
/**
 * API Response Yardımcı Sınıfı
 * Emlak-Delfino Projesi
 */

class Response {
    
    /**
     * Başarılı yanıt döndürür
     */
    public static function success($data = null, $message = 'İşlem başarılı', $code = 200) {
        http_response_code($code);
        
        $response = [
            'status' => 'success',
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Hata yanıtı döndürür
     */
    public static function error($message = 'Bir hata oluştu', $code = 400, $errors = null) {
        http_response_code($code);
        
        $response = [
            'status' => 'error',
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Sayfalama ile birlikte başarılı yanıt döndürür
     */
    public static function successWithPagination($data, $pagination, $message = 'İşlem başarılı') {
        self::success([
            'items' => $data,
            'pagination' => $pagination
        ], $message);
    }
    
    /**
     * Yetkilendirme hatası döndürür
     */
    public static function unauthorized($message = 'Yetkisiz erişim') {
        self::error($message, 401);
    }
    
    /**
     * Bulunamadı hatası döndürür
     */
    public static function notFound($message = 'Kayıt bulunamadı') {
        self::error($message, 404);
    }
    
    /**
     * Validasyon hatası döndürür
     */
    public static function validationError($errors, $message = 'Validasyon hatası') {
        self::error($message, 422, $errors);
    }
}
?> 