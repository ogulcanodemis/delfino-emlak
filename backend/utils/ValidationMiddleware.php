<?php
/**
 * Validation Middleware
 * Emlak-Delfino Projesi
 * Input validasyonu ve veri doğrulama
 */

require_once __DIR__ . '/Response.php';

class ValidationMiddleware {
    
    /**
     * JSON input'u doğrula ve parse et
     */
    public static function validateJsonInput($required_fields = []) {
        $input = file_get_contents('php://input');
        
        if (empty($input)) {
            Response::validationError(['message' => 'JSON verisi gereklidir']);
        }
        
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::validationError(['message' => 'Geçersiz JSON formatı']);
        }
        
        // Gerekli alanları kontrol et
        if (!empty($required_fields)) {
            $missing_fields = [];
            foreach ($required_fields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    $missing_fields[] = $field;
                }
            }
            
            if (!empty($missing_fields)) {
                Response::validationError([
                    'message' => 'Gerekli alanlar eksik',
                    'missing_fields' => $missing_fields
                ]);
            }
        }
        
        return $data;
    }

    /**
     * E-posta formatını doğrula
     */
    public static function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Türkiye'de yaygın e-posta sağlayıcıları için ek kontrol
        $allowed_domains = [
            'gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com',
            'yandex.com', 'mynet.com', 'turk.net', 'superonline.com'
        ];
        
        $domain = substr(strrchr($email, "@"), 1);
        
        return true; // Tüm domainlere izin ver (kısıtlama istenirse kullanılabilir)
    }

    /**
     * Şifre güçlülüğünü kontrol et
     */
    public static function validatePassword($password) {
        $errors = [];
        
        if (strlen($password) < 6) {
            $errors[] = 'Şifre en az 6 karakter olmalıdır';
        }
        
        if (strlen($password) > 50) {
            $errors[] = 'Şifre en fazla 50 karakter olabilir';
        }
        
        // Güçlü şifre kontrolü (opsiyonel)
        /*
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Şifre en az bir büyük harf içermelidir';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Şifre en az bir küçük harf içermelidir';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Şifre en az bir rakam içermelidir';
        }
        */
        
        return empty($errors) ? true : $errors;
    }

    /**
     * Telefon numarasını doğrula (Türkiye formatı)
     */
    public static function validatePhone($phone) {
        if (empty($phone)) {
            return true; // Telefon opsiyonel
        }
        
        // Türkiye telefon formatları: +90, 0, 90 ile başlayan
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        $patterns = [
            '/^(\+90|90)?[0-9]{10}$/',  // +905551234567, 905551234567, 5551234567
            '/^0[0-9]{10}$/'            // 05551234567
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Fiyat formatını doğrula
     */
    public static function validatePrice($price) {
        if (!is_numeric($price)) {
            return false;
        }
        
        $price = (float)$price;
        
        if ($price < 0) {
            return false;
        }
        
        if ($price > 999999999.99) { // 999 milyon TL limit
            return false;
        }
        
        return true;
    }

    /**
     * Metrekare değerini doğrula
     */
    public static function validateArea($area) {
        if (!is_numeric($area)) {
            return false;
        }
        
        $area = (int)$area;
        
        if ($area < 1 || $area > 50000) { // 1-50000 m² arası
            return false;
        }
        
        return true;
    }

    /**
     * Oda sayısını doğrula
     */
    public static function validateRooms($rooms) {
        if (empty($rooms)) {
            return true; // Opsiyonel
        }
        
        if (!is_numeric($rooms)) {
            return false;
        }
        
        $rooms = (int)$rooms;
        
        if ($rooms < 0 || $rooms > 20) { // 0-20 oda arası
            return false;
        }
        
        return true;
    }

    /**
     * Koordinatları doğrula
     */
    public static function validateCoordinates($latitude, $longitude) {
        if (empty($latitude) || empty($longitude)) {
            return true; // Opsiyonel
        }
        
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return false;
        }
        
        $lat = (float)$latitude;
        $lng = (float)$longitude;
        
        // Türkiye koordinat sınırları (yaklaşık)
        if ($lat < 35.0 || $lat > 43.0) {
            return false;
        }
        
        if ($lng < 25.0 || $lng > 45.0) {
            return false;
        }
        
        return true;
    }

    /**
     * Dosya yükleme doğrulaması
     */
    public static function validateFileUpload($file, $allowed_types = [], $max_size = 5242880) { // 5MB default
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'Dosya yükleme hatası'];
        }
        
        // Dosya boyutu kontrolü
        if ($file['size'] > $max_size) {
            return ['valid' => false, 'message' => 'Dosya boyutu çok büyük'];
        }
        
        // Dosya tipi kontrolü
        if (!empty($allowed_types) && !in_array($file['type'], $allowed_types)) {
            return ['valid' => false, 'message' => 'Desteklenmeyen dosya formatı'];
        }
        
        // Güvenlik kontrolü - gerçek dosya mı?
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if ($mime_type !== $file['type']) {
            return ['valid' => false, 'message' => 'Dosya tipi uyumsuzluğu'];
        }
        
        return ['valid' => true, 'message' => 'Dosya geçerli'];
    }

    /**
     * Sayfalama parametrelerini doğrula
     */
    public static function validatePagination($page = 1, $limit = 10, $max_limit = 100) {
        $page = max(1, (int)$page);
        $limit = max(1, min($max_limit, (int)$limit));
        
        return ['page' => $page, 'limit' => $limit];
    }

    /**
     * Arama terimini temizle ve doğrula
     */
    public static function validateSearchTerm($search) {
        if (empty($search)) {
            return '';
        }
        
        // HTML ve script taglarını temizle
        $search = strip_tags($search);
        
        // Özel karakterleri temizle
        $search = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
        
        // Minimum 2 karakter
        if (strlen($search) < 2) {
            return '';
        }
        
        // Maksimum 100 karakter
        if (strlen($search) > 100) {
            $search = substr($search, 0, 100);
        }
        
        return trim($search);
    }

    /**
     * ID parametresini doğrula
     */
    public static function validateId($id) {
        if (!is_numeric($id)) {
            Response::validationError(['message' => 'Geçersiz ID formatı']);
        }
        
        $id = (int)$id;
        
        if ($id <= 0) {
            Response::validationError(['message' => 'ID pozitif bir sayı olmalıdır']);
        }
        
        return $id;
    }

    /**
     * Metin uzunluğunu doğrula
     */
    public static function validateTextLength($text, $min_length = 0, $max_length = 1000) {
        $length = strlen($text);
        
        if ($length < $min_length) {
            return false;
        }
        
        if ($length > $max_length) {
            return false;
        }
        
        return true;
    }

    /**
     * XSS koruması için input temizleme
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * SQL injection koruması için string temizleme
     */
    public static function sanitizeString($string) {
        return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Emlak özelliklerini doğrula
     */
    public static function validatePropertyData($data) {
        $errors = [];
        
        // Başlık kontrolü
        if (empty($data['title']) || !self::validateTextLength($data['title'], 5, 255)) {
            $errors['title'] = 'Başlık 5-255 karakter arasında olmalıdır';
        }
        
        // Açıklama kontrolü
        if (empty($data['description']) || !self::validateTextLength($data['description'], 10, 5000)) {
            $errors['description'] = 'Açıklama 10-5000 karakter arasında olmalıdır';
        }
        
        // Fiyat kontrolü
        if (empty($data['price']) || !self::validatePrice($data['price'])) {
            $errors['price'] = 'Geçerli bir fiyat giriniz';
        }
        
        // Alan kontrolü
        if (empty($data['area']) || !self::validateArea($data['area'])) {
            $errors['area'] = 'Geçerli bir metrekare değeri giriniz (1-50000)';
        }
        
        // Koordinat kontrolü
        if (isset($data['latitude']) && isset($data['longitude'])) {
            if (!self::validateCoordinates($data['latitude'], $data['longitude'])) {
                $errors['coordinates'] = 'Geçersiz koordinat bilgisi';
            }
        }
        
        return empty($errors) ? true : $errors;
    }
}
?> 