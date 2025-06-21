<?php

class FileUploadService {
    private $upload_path;
    private $allowed_types;
    private $max_file_size;
    private $max_files_per_property;
    
    public function __construct() {
        $this->upload_path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/properties/';
        $this->allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $this->max_file_size = 5 * 1024 * 1024; // 5MB
        $this->max_files_per_property = 20;
        
        // Upload klasörünü oluştur
        $this->createUploadDirectory();
    }
    
    // Upload klasörünü oluştur
    private function createUploadDirectory() {
        if (!file_exists($this->upload_path)) {
            mkdir($this->upload_path, 0755, true);
        }
        
        // Yıl ve ay klasörlerini oluştur
        $year = date('Y');
        $month = date('m');
        
        $year_path = $this->upload_path . $year . '/';
        $month_path = $year_path . $month . '/';
        
        if (!file_exists($year_path)) {
            mkdir($year_path, 0755, true);
        }
        
        if (!file_exists($month_path)) {
            mkdir($month_path, 0755, true);
        }
    }
    
    // Tek dosya yükle
    public function uploadSingle($file, $property_id, $alt_text = '') {
        try {
            // Dosya kontrolü
            $validation = $this->validateFile($file, $property_id);
            if (!$validation['success']) {
                return $validation;
            }
            
            // Dosya adını oluştur
            $file_info = $this->generateFileName($file);
            $upload_path = $this->getUploadPath() . $file_info['filename'];
            
            // Dosyayı yükle
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Resim boyutlarını al
                $image_info = getimagesize($upload_path);
                
                // Relative path oluştur (web erişimi için)
                $relative_path = str_replace(__DIR__ . '/../../', '', $upload_path);
                
                return [
                    'success' => true,
                    'message' => 'Dosya başarıyla yüklendi',
                    'data' => [
                        'image_path' => $relative_path,
                        'image_name' => $file_info['original_name'],
                        'image_size' => filesize($upload_path),
                        'image_type' => $file['type'],
                        'alt_text' => $alt_text,
                        'width' => $image_info[0] ?? null,
                        'height' => $image_info[1] ?? null
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Dosya yüklenirken hata oluştu'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Dosya yükleme hatası: ' . $e->getMessage()
            ];
        }
    }
    
    // Çoklu dosya yükle
    public function uploadMultiple($files, $property_id) {
        $results = [];
        $success_count = 0;
        $error_count = 0;
        
        // Dosya sayısı kontrolü
        if (count($files['name']) > $this->max_files_per_property) {
            return [
                'success' => false,
                'message' => "En fazla {$this->max_files_per_property} dosya yükleyebilirsiniz"
            ];
        }
        
        for ($i = 0; $i < count($files['name']); $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            $result = $this->uploadSingle($file, $property_id);
            $results[] = $result;
            
            if ($result['success']) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
        
        return [
            'success' => $success_count > 0,
            'message' => "{$success_count} dosya başarıyla yüklendi, {$error_count} dosya yüklenemedi",
            'results' => $results,
            'stats' => [
                'success_count' => $success_count,
                'error_count' => $error_count,
                'total_count' => count($files['name'])
            ]
        ];
    }
    
    // Dosya doğrulama
    private function validateFile($file, $property_id) {
        // Upload hatası kontrolü
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => $this->getUploadErrorMessage($file['error'])
            ];
        }
        
        // Dosya boyutu kontrolü
        if ($file['size'] > $this->max_file_size) {
            return [
                'success' => false,
                'message' => 'Dosya boyutu çok büyük (Max: ' . $this->formatBytes($this->max_file_size) . ')'
            ];
        }
        
        // Dosya tipi kontrolü
        if (!in_array($file['type'], $this->allowed_types)) {
            return [
                'success' => false,
                'message' => 'Desteklenmeyen dosya formatı'
            ];
        }
        
        // Gerçek resim dosyası kontrolü
        $image_info = getimagesize($file['tmp_name']);
        if ($image_info === false) {
            return [
                'success' => false,
                'message' => 'Geçersiz resim dosyası'
            ];
        }
        
        // Emlak için maksimum dosya sayısı kontrolü
        require_once __DIR__ . '/../models/PropertyImage.php';
        $propertyImage = new PropertyImage();
        $current_count = $propertyImage->getImageCount($property_id);
        
        if ($current_count >= $this->max_files_per_property) {
            return [
                'success' => false,
                'message' => "Bu emlak için maksimum {$this->max_files_per_property} resim yükleyebilirsiniz"
            ];
        }
        
        return ['success' => true];
    }
    
    // Dosya adı oluştur
    private function generateFileName($file) {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $original_name = pathinfo($file['name'], PATHINFO_FILENAME);
        
        // Güvenli dosya adı oluştur
        $safe_name = $this->sanitizeFileName($original_name);
        $unique_name = $safe_name . '_' . uniqid() . '_' . time() . '.' . $extension;
        
        return [
            'filename' => $unique_name,
            'original_name' => $file['name'],
            'safe_name' => $safe_name,
            'extension' => $extension
        ];
    }
    
    // Dosya adını güvenli hale getir
    private function sanitizeFileName($filename) {
        // Türkçe karakterleri değiştir
        $turkish = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'I', 'İ', 'Ö', 'Ş', 'Ü'];
        $english = ['c', 'g', 'i', 'o', 's', 'u', 'C', 'G', 'I', 'I', 'O', 'S', 'U'];
        $filename = str_replace($turkish, $english, $filename);
        
        // Özel karakterleri kaldır
        $filename = preg_replace('/[^a-zA-Z0-9\-_]/', '', $filename);
        
        // Çoklu tire ve alt çizgileri tek yap
        $filename = preg_replace('/[-_]+/', '-', $filename);
        
        // Başında ve sonunda tire varsa kaldır
        $filename = trim($filename, '-_');
        
        // Boşsa varsayılan ad ver
        if (empty($filename)) {
            $filename = 'image';
        }
        
        return $filename;
    }
    
    // Upload yolu getir
    private function getUploadPath() {
        $year = date('Y');
        $month = date('m');
        return $this->upload_path . $year . '/' . $month . '/';
    }
    
    // Dosya sil
    public function deleteFile($file_path) {
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        return true; // Dosya zaten yoksa başarılı say
    }
    
    // Resim boyutlandır
    public function resizeImage($source_path, $destination_path, $max_width = 1200, $max_height = 800, $quality = 85) {
        try {
            $image_info = getimagesize($source_path);
            if (!$image_info) {
                return false;
            }
            
            $original_width = $image_info[0];
            $original_height = $image_info[1];
            $image_type = $image_info[2];
            
            // Boyutlandırma gerekli mi kontrol et
            if ($original_width <= $max_width && $original_height <= $max_height) {
                return copy($source_path, $destination_path);
            }
            
            // Yeni boyutları hesapla
            $ratio = min($max_width / $original_width, $max_height / $original_height);
            $new_width = round($original_width * $ratio);
            $new_height = round($original_height * $ratio);
            
            // Kaynak resmi yükle
            switch ($image_type) {
                case IMAGETYPE_JPEG:
                    $source_image = imagecreatefromjpeg($source_path);
                    break;
                case IMAGETYPE_PNG:
                    $source_image = imagecreatefrompng($source_path);
                    break;
                case IMAGETYPE_GIF:
                    $source_image = imagecreatefromgif($source_path);
                    break;
                case IMAGETYPE_WEBP:
                    $source_image = imagecreatefromwebp($source_path);
                    break;
                default:
                    return false;
            }
            
            if (!$source_image) {
                return false;
            }
            
            // Yeni resim oluştur
            $new_image = imagecreatetruecolor($new_width, $new_height);
            
            // PNG ve GIF için şeffaflığı koru
            if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
                $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
                imagefill($new_image, 0, 0, $transparent);
            }
            
            // Resmi boyutlandır
            imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
            
            // Resmi kaydet
            $result = false;
            switch ($image_type) {
                case IMAGETYPE_JPEG:
                    $result = imagejpeg($new_image, $destination_path, $quality);
                    break;
                case IMAGETYPE_PNG:
                    $result = imagepng($new_image, $destination_path, 9);
                    break;
                case IMAGETYPE_GIF:
                    $result = imagegif($new_image, $destination_path);
                    break;
                case IMAGETYPE_WEBP:
                    $result = imagewebp($new_image, $destination_path, $quality);
                    break;
            }
            
            // Belleği temizle
            imagedestroy($source_image);
            imagedestroy($new_image);
            
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Thumbnail oluştur
    public function createThumbnail($source_path, $thumbnail_path, $size = 300) {
        return $this->resizeImage($source_path, $thumbnail_path, $size, $size, 80);
    }
    
    // Upload hata mesajları
    private function getUploadErrorMessage($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'Dosya boyutu çok büyük (server limit)';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Dosya boyutu çok büyük (form limit)';
            case UPLOAD_ERR_PARTIAL:
                return 'Dosya kısmen yüklendi';
            case UPLOAD_ERR_NO_FILE:
                return 'Dosya seçilmedi';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Geçici klasör bulunamadı';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Dosya yazılamadı';
            case UPLOAD_ERR_EXTENSION:
                return 'Dosya uzantısı engellendi';
            default:
                return 'Bilinmeyen upload hatası';
        }
    }
    
    // Byte formatla
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    // Disk kullanımı getir
    public function getDiskUsage() {
        $total_size = 0;
        $file_count = 0;
        
        if (!is_dir($this->upload_path)) {
            return [
                'total_size' => 0,
                'formatted_size' => '0 B',
                'file_count' => 0
            ];
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->upload_path)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $total_size += $file->getSize();
                $file_count++;
            }
        }
        
        return [
            'total_size' => $total_size,
            'formatted_size' => $this->formatBytes($total_size),
            'file_count' => $file_count
        ];
    }
    
    // Ayarları getir
    public function getSettings() {
        return [
            'upload_path' => $this->upload_path,
            'allowed_types' => $this->allowed_types,
            'max_file_size' => $this->max_file_size,
            'max_file_size_formatted' => $this->formatBytes($this->max_file_size),
            'max_files_per_property' => $this->max_files_per_property
        ];
    }
} 