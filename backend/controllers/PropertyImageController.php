<?php
require_once __DIR__ . '/../models/PropertyImage.php';
require_once __DIR__ . '/../services/FileUploadService.php';
require_once __DIR__ . '/../utils/JWT.php';

class PropertyImageController {
    private $propertyImage;
    private $fileUploadService;
    
    public function __construct() {
        $this->propertyImage = new PropertyImage();
        $this->fileUploadService = new FileUploadService();
    }
    
    // Emlağa ait resimleri listele
    public function getByProperty($property_id) {
        try {
            if (empty($property_id)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Emlak ID gereklidir'
                ]);
                return;
            }
            
            $images = $this->propertyImage->getByPropertyId($property_id);
            
            // Resim URL'lerini düzenle
            foreach ($images as &$image) {
                $image['image_url'] = $this->getImageUrl($image['image_path']);
                $image['thumbnail_url'] = $this->getThumbnailUrl($image['image_path']);
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Resimler başarıyla getirildi',
                'data' => $images,
                'count' => count($images)
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Resimler getirilirken hata oluştu: ' . $e->getMessage()
            ]);
        }
    }
    
    // Resim detayını getir
    public function getById($id) {
        try {
            if (empty($id)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Resim ID gereklidir'
                ]);
                return;
            }
            
            $image = $this->propertyImage->getById($id);
            
            if (!$image) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Resim bulunamadı'
                ]);
                return;
            }
            
            // Resim URL'lerini ekle
            $image['image_url'] = $this->getImageUrl($image['image_path']);
            $image['thumbnail_url'] = $this->getThumbnailUrl($image['image_path']);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Resim başarıyla getirildi',
                'data' => $image
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Resim getirilirken hata oluştu: ' . $e->getMessage()
            ]);
        }
    }
    
    // Tek resim yükle
    public function uploadSingle() {
        try {
            // JWT token kontrolü
            $user = $this->authenticateUser();
            if (!$user) {
                return;
            }
            
            // POST verilerini kontrol et
            if (!isset($_POST['property_id'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Emlak ID gereklidir'
                ]);
                return;
            }
            
            $property_id = $_POST['property_id'];
            $alt_text = $_POST['alt_text'] ?? '';
            $is_primary = isset($_POST['is_primary']) ? (bool)$_POST['is_primary'] : false;
            
            // Emlak sahibi kontrolü
            if (!$this->checkPropertyOwnership($property_id, $user['id'])) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Bu emlağa resim yükleme yetkiniz yok'
                ]);
                return;
            }
            
            // Dosya kontrolü
            if (!isset($_FILES['image'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Resim dosyası seçilmedi'
                ]);
                return;
            }
            
            // Dosyayı yükle
            $upload_result = $this->fileUploadService->uploadSingle($_FILES['image'], $property_id, $alt_text);
            
            if (!$upload_result['success']) {
                http_response_code(400);
                echo json_encode($upload_result);
                return;
            }
            
            // Veritabanına kaydet
            $this->propertyImage->property_id = $property_id;
            $this->propertyImage->image_path = $upload_result['data']['image_path'];
            $this->propertyImage->image_name = $upload_result['data']['image_name'];
            $this->propertyImage->image_size = $upload_result['data']['image_size'];
            $this->propertyImage->image_type = $upload_result['data']['image_type'];
            $this->propertyImage->alt_text = $alt_text;
            $this->propertyImage->is_primary = $is_primary;
            
            // Validation
            $validation_errors = $this->propertyImage->validate();
            if (!empty($validation_errors)) {
                // Yüklenen dosyayı sil
                $this->fileUploadService->deleteFile($upload_result['data']['image_path']);
                
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Validation hatası',
                    'errors' => $validation_errors
                ]);
                return;
            }
            
            if ($this->propertyImage->create()) {
                $image_data = $this->propertyImage->getById($this->propertyImage->id);
                $image_data['image_url'] = $this->getImageUrl($image_data['image_path']);
                $image_data['thumbnail_url'] = $this->getThumbnailUrl($image_data['image_path']);
                
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Resim başarıyla yüklendi',
                    'data' => $image_data
                ]);
            } else {
                // Yüklenen dosyayı sil
                $this->fileUploadService->deleteFile($upload_result['data']['image_path']);
                
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Resim veritabanına kaydedilemedi'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Resim yüklenirken hata oluştu: ' . $e->getMessage()
            ]);
        }
    }
    
    // Çoklu resim yükle
    public function uploadMultiple() {
        try {
            // JWT token kontrolü
            $user = $this->authenticateUser();
            if (!$user) {
                return;
            }
            
            // POST verilerini kontrol et
            if (!isset($_POST['property_id'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Emlak ID gereklidir'
                ]);
                return;
            }
            
            $property_id = $_POST['property_id'];
            
            // Emlak sahibi kontrolü
            if (!$this->checkPropertyOwnership($property_id, $user['id'])) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Bu emlağa resim yükleme yetkiniz yok'
                ]);
                return;
            }
            
            // Dosya kontrolü
            if (empty($_FILES)) {
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'message' => 'Resim dosyaları seçilmedi',
                    'debug' => 'No files in $_FILES'
                ]);
                return;
            }
            
            // images[] formatını kontrol et
            $files_array = null;
            if (isset($_FILES['images'])) {
                $files_array = $_FILES['images'];
            } else {
                // $_FILES array'ini kontrol et ve ilk uygun array'i bul
                foreach ($_FILES as $key => $file_data) {
                    if (is_array($file_data) && isset($file_data['name'])) {
                        $files_array = $file_data;
                        break;
                    }
                }
            }
            
            if (!$files_array) {
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'message' => 'Resim dosyaları bulunamadı',
                    'debug' => array_keys($_FILES)
                ]);
                return;
            }
            
            // Dosyaları yükle
            $upload_result = $this->fileUploadService->uploadMultiple($files_array, $property_id);
            
            $saved_images = [];
            $failed_images = [];
            
            // Başarılı yüklemeleri veritabanına kaydet
            foreach ($upload_result['results'] as $index => $result) {
                if ($result['success']) {
                    $this->propertyImage = new PropertyImage(); // Yeni instance
                    $this->propertyImage->property_id = $property_id;
                    $this->propertyImage->image_path = $result['data']['image_path'];
                    $this->propertyImage->image_name = $result['data']['image_name'];
                    $this->propertyImage->image_size = $result['data']['image_size'];
                    $this->propertyImage->image_type = $result['data']['image_type'];
                    $this->propertyImage->alt_text = $result['data']['alt_text'];
                    $this->propertyImage->is_primary = 0; // Çoklu yüklemede ana resim belirlenmez
                    
                    if ($this->propertyImage->create()) {
                        $image_data = $this->propertyImage->getById($this->propertyImage->id);
                        $image_data['image_url'] = $this->getImageUrl($image_data['image_path']);
                        $image_data['thumbnail_url'] = $this->getThumbnailUrl($image_data['image_path']);
                        $saved_images[] = $image_data;
                    } else {
                        // Veritabanına kaydedilemezse dosyayı sil
                        $this->fileUploadService->deleteFile($result['data']['image_path']);
                        $failed_images[] = [
                            'index' => $index,
                            'message' => 'Veritabanına kaydedilemedi'
                        ];
                    }
                } else {
                    $failed_images[] = [
                        'index' => $index,
                        'message' => $result['message']
                    ];
                }
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => count($saved_images) > 0,
                'message' => count($saved_images) . ' resim başarıyla yüklendi, ' . count($failed_images) . ' resim yüklenemedi',
                'data' => [
                    'saved_images' => $saved_images,
                    'failed_images' => $failed_images,
                    'stats' => [
                        'success_count' => count($saved_images),
                        'error_count' => count($failed_images),
                        'total_count' => count($upload_result['results'])
                    ]
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Resimler yüklenirken hata oluştu: ' . $e->getMessage()
            ]);
        }
    }
    
    // Resim güncelle
    public function update($id) {
        try {
            // JWT token kontrolü
            $user = $this->authenticateUser();
            if (!$user) {
                return;
            }
            
            if (empty($id)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Resim ID gereklidir'
                ]);
                return;
            }
            
            // Mevcut resmi kontrol et
            $existing_image = $this->propertyImage->getById($id);
            if (!$existing_image) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Resim bulunamadı'
                ]);
                return;
            }
            
            // Emlak sahibi kontrolü
            if (!$this->checkPropertyOwnership($existing_image['property_id'], $user['id'])) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Bu resmi güncelleme yetkiniz yok'
                ]);
                return;
            }
            
            // JSON verisini al
            $input = json_decode(file_get_contents('php://input'), true);
            
            $this->propertyImage->id = $id;
            $this->propertyImage->property_id = $existing_image['property_id'];
            $this->propertyImage->image_name = $input['image_name'] ?? $existing_image['image_name'];
            $this->propertyImage->alt_text = $input['alt_text'] ?? $existing_image['alt_text'];
            $this->propertyImage->is_primary = isset($input['is_primary']) ? (bool)$input['is_primary'] : (bool)$existing_image['is_primary'];
            $this->propertyImage->display_order = $input['display_order'] ?? $existing_image['display_order'];
            
            if ($this->propertyImage->update()) {
                $updated_image = $this->propertyImage->getById($id);
                $updated_image['image_url'] = $this->getImageUrl($updated_image['image_path']);
                $updated_image['thumbnail_url'] = $this->getThumbnailUrl($updated_image['image_path']);
                
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Resim başarıyla güncellendi',
                    'data' => $updated_image
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Resim güncellenirken hata oluştu'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Resim güncellenirken hata oluştu: ' . $e->getMessage()
            ]);
        }
    }
    
    // Resim sil
    public function delete($id) {
        try {
            // JWT token kontrolü
            $user = $this->authenticateUser();
            if (!$user) {
                return;
            }
            
            if (empty($id)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Resim ID gereklidir'
                ]);
                return;
            }
            
            // Mevcut resmi kontrol et
            $existing_image = $this->propertyImage->getById($id);
            if (!$existing_image) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Resim bulunamadı'
                ]);
                return;
            }
            
            // Emlak sahibi kontrolü
            if (!$this->checkPropertyOwnership($existing_image['property_id'], $user['id'])) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Bu resmi silme yetkiniz yok'
                ]);
                return;
            }
            
            $this->propertyImage->id = $id;
            $result = $this->propertyImage->delete();
            
            if ($result['success']) {
                http_response_code(200);
                echo json_encode($result);
            } else {
                http_response_code(500);
                echo json_encode($result);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Resim silinirken hata oluştu: ' . $e->getMessage()
            ]);
        }
    }
    
    // Ana resim belirle
    public function setPrimary($id) {
        try {
            // JWT token kontrolü
            $user = $this->authenticateUser();
            if (!$user) {
                return;
            }
            
            if (empty($id)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Resim ID gereklidir'
                ]);
                return;
            }
            
            // Mevcut resmi kontrol et
            $existing_image = $this->propertyImage->getById($id);
            if (!$existing_image) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Resim bulunamadı'
                ]);
                return;
            }
            
            // Emlak sahibi kontrolü
            if (!$this->checkPropertyOwnership($existing_image['property_id'], $user['id'])) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Bu işlemi yapma yetkiniz yok'
                ]);
                return;
            }
            
            if ($this->propertyImage->setPrimary($id, $existing_image['property_id'])) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Ana resim başarıyla belirlendi'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Ana resim belirlenirken hata oluştu'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Ana resim belirlenirken hata oluştu: ' . $e->getMessage()
            ]);
        }
    }
    
    // Resim sırasını güncelle
    public function updateOrder() {
        try {
            // JWT token kontrolü
            $user = $this->authenticateUser();
            if (!$user) {
                return;
            }
            
            // JSON verisini al
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['images_order']) || !is_array($input['images_order'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Resim sırası verisi gereklidir'
                ]);
                return;
            }
            
            // İlk resmin emlak sahibi kontrolü
            if (!empty($input['images_order'])) {
                $first_image_id = reset($input['images_order']);
                $first_image = $this->propertyImage->getById($first_image_id);
                
                if (!$first_image || !$this->checkPropertyOwnership($first_image['property_id'], $user['id'])) {
                    http_response_code(403);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Bu işlemi yapma yetkiniz yok'
                    ]);
                    return;
                }
            }
            
            if ($this->propertyImage->updateDisplayOrder($input['images_order'])) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Resim sırası başarıyla güncellendi'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Resim sırası güncellenirken hata oluştu'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Resim sırası güncellenirken hata oluştu: ' . $e->getMessage()
            ]);
        }
    }
    
    // İstatistikler
    public function getStats() {
        try {
            // JWT token kontrolü (admin)
            $user = $this->authenticateUser();
            $userRole = isset($user['role']) ? $user['role'] : 'user';
            if (!$user || $userRole !== 'super_admin') {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Bu işlemi yapma yetkiniz yok'
                ]);
                return;
            }
            
            $stats = $this->propertyImage->getStats();
            $disk_usage = $this->fileUploadService->getDiskUsage();
            $settings = $this->fileUploadService->getSettings();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'İstatistikler başarıyla getirildi',
                'data' => [
                    'image_stats' => $stats,
                    'disk_usage' => $disk_usage,
                    'settings' => $settings
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'İstatistikler getirilirken hata oluştu: ' . $e->getMessage()
            ]);
        }
    }
    
    // Kullanıcı kimlik doğrulama
    private function authenticateUser() {
        $headers = getallheaders();
        $token = null;
        
        if (isset($headers['Authorization'])) {
            $auth_header = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
                $token = $matches[1];
            }
        }
        
        if (!$token) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Token gereklidir'
            ]);
            return false;
        }
        
        try {
            $decoded = JWT::decode($token);
            if ($decoded === false) {
                throw new Exception('Invalid token');
            }
            return [
                'id' => $decoded['user_id'],
                'email' => $decoded['email'],
                'role' => isset($decoded['role']) ? $decoded['role'] : 'user'
            ];
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Geçersiz token'
            ]);
            return false;
        }
    }
    
    // Emlak sahipliği kontrolü
    private function checkPropertyOwnership($property_id, $user_id) {
        require_once __DIR__ . '/../models/Property.php';
        require_once __DIR__ . '/../config/database.php';
        
        $database = new Database();
        $db = $database->getConnection();
        $property = new Property($db);
        $property_data = $property->getById($property_id);
        
        // Debug logging
        error_log("PropertyOwnership Check - Property ID: " . $property_id . ", User ID: " . $user_id);
        if ($property_data) {
            error_log("Property Data - Owner ID: " . $property_data['user_id'] . " (type: " . gettype($property_data['user_id']) . ")");
            error_log("Comparison: " . (int)$property_data['user_id'] . " === " . (int)$user_id);
        } else {
            error_log("Property not found for ID: " . $property_id);
        }
        
        return $property_data && (int)$property_data['user_id'] === (int)$user_id;
    }
    
    // Resim URL'si oluştur
    private function getImageUrl($image_path) {
        // Base URL'i tanımla
        $base_url = 'https://bkyatirim.com';
        
        // Eğer zaten tam URL ise direkt döndür
        if (strpos($image_path, 'http') === 0) {
            return $image_path;
        }
        
        // Relative path ise tam URL'e çevir
        if (strpos($image_path, 'uploads/') === 0) {
            return $base_url . '/' . $image_path;
        }
        
        // Absolute path ise relative'e çevirip tam URL yap
        $web_path = str_replace(__DIR__ . '/../../', '', $image_path);
        return $base_url . '/' . str_replace('\\', '/', $web_path);
    }
    
    // Thumbnail URL'si oluştur
    private function getThumbnailUrl($image_path) {
        // Basit thumbnail URL'si - gerçek uygulamada thumbnail oluşturma servisi kullanılabilir
        return $this->getImageUrl($image_path) . '?thumb=1';
    }
}
