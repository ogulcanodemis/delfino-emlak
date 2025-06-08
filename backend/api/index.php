<?php
/**
 * Emlak-Delfino API Ana Giriş Noktası
 * Tüm API istekleri bu dosya üzerinden yönlendirilir
 */

// CORS ayarları - temizle ve yeniden ayarla
header_remove('Access-Control-Allow-Origin');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// OPTIONS isteği için
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Gerekli dosyaları dahil et
require_once '../config/database.php';
require_once '../utils/Response.php';

// Hata raporlamayı aç (geliştirme aşamasında)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// İstek metodunu al
$method = $_SERVER['REQUEST_METHOD'];

// URL'den endpoint'i al
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// API prefix'ini kaldır
$path = str_replace('/emlak-delfino/backend/api', '', $path);
$path = trim($path, '/');

// Path'i parçalara ayır
$segments = explode('/', $path);

try {
    // Veritabanı bağlantısını test et
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        Response::error('Veritabanı bağlantısı kurulamadı', 500);
    }
    
    // Ana routing
    if (empty($segments[0])) {
        // Ana endpoint - API durumu
        Response::success([
            'api_name' => 'Emlak-Delfino API',
            'version' => '1.0.0',
            'status' => 'active',
            'endpoints' => [
                'test' => '/api/test',
                'properties' => '/api/properties',
                'auth' => '/api/auth',
                'cities' => '/api/cities',
                'districts' => '/api/districts',
                'neighborhoods' => '/api/neighborhoods',
                'locations' => '/api/locations',
                'favorites' => '/api/favorites',
                'property-statuses' => '/api/property-statuses',
                'property-images' => '/api/property-images',
                'role-requests' => '/api/role-requests',
                'admin' => '/api/admin'
            ]
        ], 'API aktif ve çalışıyor');
    }
    
    switch ($segments[0]) {
        case 'test':
            // Test endpoint'i
            handleTestEndpoint($database);
            break;
            
        case 'cities':
            // Şehirler endpoint'i
            handleCitiesEndpoint($db, $method, $segments);
            break;
            
        case 'districts':
            // İlçeler endpoint'i
            handleDistrictsEndpoint($db, $method, $segments);
            break;
            
        case 'neighborhoods':
            // Mahalleler endpoint'i
            handleNeighborhoodsEndpoint($db, $method, $segments);
            break;
            
        case 'locations':
            // Konum hiyerarşisi endpoint'i
            handleLocationsEndpoint($db, $method, $segments);
            break;
            
        case 'auth':
            // Authentication endpoint'i
            handleAuthEndpoint($db, $method, $segments);
            break;
            
        case 'properties':
            // İlanlar endpoint'i (şimdilik basit)
            handlePropertiesEndpoint($db, $method, $segments);
            break;
            
        case 'favorites':
            // Favoriler endpoint'i
            handleFavoritesEndpoint($db, $method, $segments);
            break;
            
        case 'property-statuses':
            // Emlak durumları endpoint'i
            handlePropertyStatusesEndpoint($db, $method, $segments);
            break;
            
        case 'property-images':
            // Emlak resimleri endpoint'i
            handlePropertyImagesEndpoint($db, $method, $segments);
            break;
            
        case 'role-requests':
            // Rol talepleri endpoint'i
            handleRoleRequestsEndpoint($db, $method, $segments);
            break;
            
        case 'admin':
            // Admin yönetim endpoint'i
            handleAdminEndpoint($db, $method, $segments);
            break;
            
        default:
            Response::notFound('Endpoint bulunamadı: ' . $segments[0]);
    }
    
} catch (Exception $e) {
    Response::error('Sunucu hatası: ' . $e->getMessage(), 500);
}

/**
 * Test endpoint'i - veritabanı bağlantısını test eder
 */
function handleTestEndpoint($database) {
    $result = $database->testConnection();
    
    if ($result['status'] === 'success') {
        Response::success($result, 'Test başarılı');
    } else {
        Response::error($result['message'], 500);
    }
}

/**
 * Şehirler endpoint'i
 */
function handleCitiesEndpoint($db, $method, $segments) {
    require_once '../controllers/LocationController.php';
    
    $locationController = new LocationController($db);
    
    if ($method !== 'GET') {
        Response::error('Bu endpoint sadece GET metodunu destekler', 405);
    }
    
    $action = $segments[1] ?? null;
    $id = $segments[2] ?? null;
    
    switch ($action) {
        case 'top':
            // En çok ilan olan şehirler
            $locationController->getTopCities();
            break;
            
        case null:
            if (is_numeric($segments[1] ?? '')) {
                // Şehir detayı
                $locationController->getCity($segments[1]);
            } else {
                // Tüm şehirler
                $locationController->getCities();
            }
            break;
            
        default:
            if (is_numeric($action)) {
                // Şehir detayı
                $locationController->getCity($action);
            } else {
                Response::notFound('Şehir endpoint bulunamadı: ' . $action);
            }
    }
}

/**
 * Authentication endpoint'i
 */
function handleAuthEndpoint($db, $method, $segments) {
    require_once '../controllers/AuthController.php';
    
    $authController = new AuthController($db);
    
    // Alt endpoint'i belirle
    $action = $segments[1] ?? '';
    
    switch ($action) {
        case 'register':
            if ($method !== 'POST') {
                Response::error('Bu endpoint sadece POST metodunu destekler', 405);
            }
            $authController->register();
            break;
            
        case 'login':
            if ($method !== 'POST') {
                Response::error('Bu endpoint sadece POST metodunu destekler', 405);
            }
            $authController->login();
            break;
            
        case 'me':
            if ($method !== 'GET') {
                Response::error('Bu endpoint sadece GET metodunu destekler', 405);
            }
            $authController->me();
            break;
            
        case 'profile':
            if ($method !== 'PUT') {
                Response::error('Bu endpoint sadece PUT metodunu destekler', 405);
            }
            $authController->updateProfile();
            break;
            
        case 'logout':
            if ($method !== 'POST') {
                Response::error('Bu endpoint sadece POST metodunu destekler', 405);
            }
            $authController->logout();
            break;
            
        case 'forgot-password':
            if ($method !== 'POST') {
                Response::error('Bu endpoint sadece POST metodunu destekler', 405);
            }
            $authController->forgotPassword();
            break;
            
        default:
            Response::notFound('Auth endpoint bulunamadı: ' . $action);
    }
}

/**
 * İlanlar endpoint'i
 */
function handlePropertiesEndpoint($db, $method, $segments) {
    require_once '../controllers/PropertyController.php';
    
    $propertyController = new PropertyController($db);
    
    // Alt endpoint'i belirle
    $property_id = $segments[1] ?? null;
    
    switch ($method) {
        case 'GET':
            if ($property_id) {
                // Tek ilan detayı
                $propertyController->getProperty($property_id);
            } else {
                // İlan listesi
                $propertyController->getProperties();
            }
            break;
            
        case 'POST':
            // Yeni ilan oluştur (sadece emlakçı ve admin)
            $propertyController->createProperty();
            break;
            
        case 'PUT':
            if (!$property_id) {
                Response::error('İlan ID gereklidir', 400);
            }
            // İlan güncelle
            $propertyController->updateProperty($property_id);
            break;
            
        case 'DELETE':
            if (!$property_id) {
                Response::error('İlan ID gereklidir', 400);
            }
            // İlan sil
            $propertyController->deleteProperty($property_id);
            break;
            
        default:
            Response::error('Desteklenmeyen HTTP metodu', 405);
    }
}

/**
 * Favoriler endpoint'i
 */
function handleFavoritesEndpoint($db, $method, $segments) {
    require_once '../controllers/FavoriteController.php';
    
    $favoriteController = new FavoriteController($db);
    
    // Alt endpoint'i belirle
    $action = $segments[1] ?? null;
    $property_id = $segments[2] ?? null;
    
    switch ($method) {
        case 'GET':
            if ($action === 'check' && $property_id) {
                // Favori durumu kontrol et
                $favoriteController->checkFavoriteStatus($property_id);
            } elseif ($action === 'ids') {
                // Favori ilan ID'lerini getir
                $favoriteController->getFavoriteIds();
            } else {
                // Favori ilanları listele
                $favoriteController->getFavorites();
            }
            break;
            
        case 'POST':
            // Favoriye ekle
            $favoriteController->addToFavorites();
            break;
            
        case 'DELETE':
            if ($action && is_numeric($action)) {
                // Belirli ilanı favorilerden çıkar
                $favoriteController->removeFromFavorites($action);
            } else {
                // Tüm favorileri temizle
                $favoriteController->clearAllFavorites();
            }
            break;
            
        default:
            Response::error('Desteklenmeyen HTTP metodu', 405);
    }
}

/**
 * İlçeler endpoint'i
 */
function handleDistrictsEndpoint($db, $method, $segments) {
    require_once '../controllers/LocationController.php';
    
    $locationController = new LocationController($db);
    
    if ($method !== 'GET') {
        Response::error('Bu endpoint sadece GET metodunu destekler', 405);
    }
    
    $action = $segments[1] ?? null;
    $id = $segments[2] ?? null;
    
    switch ($action) {
        case 'detail':
            if (!$id) {
                Response::error('İlçe ID gereklidir', 400);
            }
            // İlçe detayı
            $locationController->getDistrict($id);
            break;
            
        case 'top':
            if (!$id) {
                Response::error('Şehir ID gereklidir', 400);
            }
            // En çok ilan olan ilçeler
            $locationController->getTopDistrictsByCity($id);
            break;
            
        default:
            if (is_numeric($action)) {
                // Şehre göre ilçeler
                $locationController->getDistrictsByCity($action);
            } else {
                Response::notFound('İlçe endpoint bulunamadı: ' . $action);
            }
    }
}

/**
 * Mahalleler endpoint'i
 */
function handleNeighborhoodsEndpoint($db, $method, $segments) {
    require_once '../controllers/LocationController.php';
    
    $locationController = new LocationController($db);
    
    if ($method !== 'GET') {
        Response::error('Bu endpoint sadece GET metodunu destekler', 405);
    }
    
    $action = $segments[1] ?? null;
    $id = $segments[2] ?? null;
    
    switch ($action) {
        case 'detail':
            if (!$id) {
                Response::error('Mahalle ID gereklidir', 400);
            }
            // Mahalle detayı
            $locationController->getNeighborhood($id);
            break;
            
        case 'by-city':
            if (!$id) {
                Response::error('Şehir ID gereklidir', 400);
            }
            // Şehre göre mahalleler
            $locationController->getNeighborhoodsByCity($id);
            break;
            
        case 'top':
            if (!$id) {
                Response::error('İlçe ID gereklidir', 400);
            }
            // En çok ilan olan mahalleler
            $locationController->getTopNeighborhoodsByDistrict($id);
            break;
            
        default:
            if (is_numeric($action)) {
                // İlçeye göre mahalleler
                $locationController->getNeighborhoodsByDistrict($action);
            } else {
                Response::notFound('Mahalle endpoint bulunamadı: ' . $action);
            }
    }
}

/**
 * Konum hiyerarşisi endpoint'i
 */
function handleLocationsEndpoint($db, $method, $segments) {
    require_once '../controllers/LocationController.php';
    
    $locationController = new LocationController($db);
    
    if ($method !== 'GET') {
        Response::error('Bu endpoint sadece GET metodunu destekler', 405);
    }
    
    $action = $segments[1] ?? null;
    
    switch ($action) {
        case 'hierarchy':
            // Konum hiyerarşisi
            $locationController->getLocationHierarchy();
            break;
            
        default:
            Response::notFound('Konum endpoint bulunamadı: ' . $action);
    }
}

/**
 * Emlak durumları endpoint'i
 */
function handlePropertyStatusesEndpoint($db, $method, $segments) {
    require_once '../controllers/PropertyStatusController.php';
    
    $propertyStatusController = new PropertyStatusController();
    
    $action = $segments[1] ?? null;
    $id = $segments[2] ?? null;
    
    switch ($method) {
        case 'GET':
            if ($action === 'stats') {
                // İstatistikler
                $propertyStatusController->getStats();
            } elseif ($action === 'most-used') {
                // En çok kullanılan durumlar
                $propertyStatusController->getMostUsed();
            } elseif ($action === 'slug' && !empty($id)) {
                // Slug ile durum getir
                $propertyStatusController->getBySlug($id);
            } elseif (is_numeric($action)) {
                // ID ile durum getir
                $propertyStatusController->show($action);
            } else {
                // Tüm durumları getir
                $propertyStatusController->index();
            }
            break;
            
        case 'POST':
            if (empty($action)) {
                // Yeni durum oluştur
                $propertyStatusController->create();
            } else {
                Response::notFound('POST endpoint bulunamadı: ' . $action);
            }
            break;
            
        case 'PUT':
            if (is_numeric($action)) {
                if ($id === 'toggle') {
                    // Durum aktif/pasif değiştir
                    $propertyStatusController->toggleStatus($action);
                } else {
                    // Durum güncelle
                    $propertyStatusController->update($action);
                }
            } else {
                Response::error('Güncellenecek durum ID gereklidir', 400);
            }
            break;
            
        case 'DELETE':
            if (is_numeric($action)) {
                // Durum sil
                $propertyStatusController->delete($action);
            } else {
                Response::error('Silinecek durum ID gereklidir', 400);
            }
            break;
            
        default:
            Response::error('Desteklenmeyen HTTP metodu: ' . $method, 405);
    }
}

/**
 * Emlak resimleri endpoint'i
 */
function handlePropertyImagesEndpoint($db, $method, $segments) {
    require_once '../controllers/PropertyImageController.php';
    
    $propertyImageController = new PropertyImageController();
    
    $action = $segments[1] ?? null;
    $id = $segments[2] ?? null;
    
    switch ($action) {
        case 'property':
            // Emlağa ait resimleri getir: /api/property-images/property/{property_id}
            if ($method !== 'GET') {
                Response::error('Bu endpoint sadece GET metodunu destekler', 405);
            }
            if (!isset($segments[2]) || !is_numeric($segments[2])) {
                Response::error('Geçerli emlak ID gereklidir', 400);
            }
            $propertyImageController->getByProperty($segments[2]);
            break;
            
        case 'upload':
            // Tek resim yükle: /api/property-images/upload
            if ($method !== 'POST') {
                Response::error('Bu endpoint sadece POST metodunu destekler', 405);
            }
            $propertyImageController->uploadSingle();
            break;
            
        case 'upload-multiple':
            // Çoklu resim yükle: /api/property-images/upload-multiple
            if ($method !== 'POST') {
                Response::error('Bu endpoint sadece POST metodunu destekler', 405);
            }
            $propertyImageController->uploadMultiple();
            break;
            
        case 'set-primary':
            // Ana resim belirle: /api/property-images/set-primary/{image_id}
            if ($method !== 'PUT') {
                Response::error('Bu endpoint sadece PUT metodunu destekler', 405);
            }
            if (!isset($segments[2]) || !is_numeric($segments[2])) {
                Response::error('Geçerli resim ID gereklidir', 400);
            }
            $propertyImageController->setPrimary($segments[2]);
            break;
            
        case 'update-order':
            // Resim sırasını güncelle: /api/property-images/update-order
            if ($method !== 'PUT') {
                Response::error('Bu endpoint sadece PUT metodunu destekler', 405);
            }
            $propertyImageController->updateOrder();
            break;
            
        case 'stats':
            // İstatistikler: /api/property-images/stats
            if ($method !== 'GET') {
                Response::error('Bu endpoint sadece GET metodunu destekler', 405);
            }
            $propertyImageController->getStats();
            break;
            
        case null:
            switch ($method) {
                case 'GET':
                    if (is_numeric($segments[1] ?? '')) {
                        // Belirli bir resmi getir
                        $propertyImageController->getById($segments[1]);
                    } else {
                        Response::error('Resim ID gereklidir', 400);
                    }
                    break;
                    
                default:
                    Response::error('Desteklenmeyen HTTP metodu', 405);
            }
            break;
            
        default:
            if (is_numeric($action)) {
                switch ($method) {
                    case 'GET':
                        $propertyImageController->getById($action);
                        break;
                        
                    case 'PUT':
                        $propertyImageController->update($action);
                        break;
                        
                    case 'DELETE':
                        $propertyImageController->delete($action);
                        break;
                        
                    default:
                        Response::error('Desteklenmeyen HTTP metodu', 405);
                }
            } else {
                Response::notFound('Resim endpoint bulunamadı: ' . $action);
            }
    }
}

/**
 * Rol talepleri endpoint'i
 */
function handleRoleRequestsEndpoint($db, $method, $segments) {
    require_once '../controllers/RoleRequestController.php';
    
    $roleRequestController = new RoleRequestController();
    
    $action = $segments[1] ?? null;
    $id = $segments[2] ?? null;
    
    switch ($action) {
        case 'my-requests':
            // Kullanıcının kendi taleplerini getir: /api/role-requests/my-requests
            if ($method !== 'GET') {
                Response::error('Bu endpoint sadece GET metodunu destekler', 405);
            }
            $roleRequestController->getMyRequests();
            break;
            
        case 'search':
            // Arama: /api/role-requests/search
            if ($method !== 'GET') {
                Response::error('Bu endpoint sadece GET metodunu destekler', 405);
            }
            $roleRequestController->search();
            break;
            
        case 'stats':
            // İstatistikler: /api/role-requests/stats
            if ($method !== 'GET') {
                Response::error('Bu endpoint sadece GET metodunu destekler', 405);
            }
            $roleRequestController->getStats();
            break;
            
        case 'review':
            // Talep onaylama/reddetme: /api/role-requests/review/{id}
            if ($method !== 'PUT') {
                Response::error('Bu endpoint sadece PUT metodunu destekler', 405);
            }
            if (!isset($segments[2]) || !is_numeric($segments[2])) {
                Response::error('Geçerli talep ID gereklidir', 400);
            }
            $roleRequestController->review($segments[2]);
            break;
            
        case null:
            switch ($method) {
                case 'GET':
                    if (is_numeric($segments[1] ?? '')) {
                        // Belirli bir talebi getir
                        $roleRequestController->getById($segments[1]);
                    } else {
                        // Tüm talepleri getir (admin için)
                        $roleRequestController->getAll();
                    }
                    break;
                    
                case 'POST':
                    // Yeni talep oluştur
                    $roleRequestController->create();
                    break;
                    
                default:
                    Response::error('Desteklenmeyen HTTP metodu', 405);
            }
            break;
            
        default:
            if (is_numeric($action)) {
                switch ($method) {
                    case 'GET':
                        $roleRequestController->getById($action);
                        break;
                        
                    case 'PUT':
                        $roleRequestController->update($action);
                        break;
                        
                    case 'DELETE':
                        $roleRequestController->delete($action);
                        break;
                        
                    default:
                        Response::error('Desteklenmeyen HTTP metodu', 405);
                }
            } else {
                Response::notFound('Rol talebi endpoint bulunamadı: ' . $action);
            }
    }
}

/**
 * Admin yönetim endpoint'i
 */
function handleAdminEndpoint($db, $method, $segments) {
    require_once '../controllers/AdminController.php';
    
    $adminController = new AdminController($db);
    
    $action = $segments[1] ?? null;
    $id = $segments[2] ?? null;
    
    switch ($action) {
        case 'dashboard':
            // Dashboard: /api/admin/dashboard
            if ($method !== 'GET') {
                Response::error('Bu endpoint sadece GET metodunu destekler', 405);
            }
            $adminController->getDashboard();
            break;
            
        case 'users':
            // Kullanıcı yönetimi: /api/admin/users
            if ($method !== 'GET') {
                Response::error('Bu endpoint sadece GET metodunu destekler', 405);
            }
            $adminController->getUsers();
            break;
            
        case 'user':
            // Kullanıcı işlemleri: /api/admin/user/{id}/{action}
            if (!isset($segments[2]) || !is_numeric($segments[2])) {
                Response::error('Geçerli kullanıcı ID gereklidir', 400);
            }
            
            $user_id = $segments[2];
            $user_action = $segments[3] ?? null;
            
            switch ($user_action) {
                case 'toggle-status':
                    // Kullanıcı durumunu değiştir: /api/admin/user/{id}/toggle-status
                    if ($method !== 'PUT') {
                        Response::error('Bu endpoint sadece PUT metodunu destekler', 405);
                    }
                    $adminController->toggleUserStatus($user_id);
                    break;
                    
                case 'change-role':
                    // Kullanıcı rolünü değiştir: /api/admin/user/{id}/change-role
                    if ($method !== 'PUT') {
                        Response::error('Bu endpoint sadece PUT metodunu destekler', 405);
                    }
                    $adminController->changeUserRole($user_id);
                    break;
                    
                default:
                    Response::notFound('Kullanıcı işlemi bulunamadı: ' . $user_action);
            }
            break;
            
        case 'properties':
            // İlan yönetimi: /api/admin/properties
            if ($method !== 'GET') {
                Response::error('Bu endpoint sadece GET metodunu destekler', 405);
            }
            $adminController->getProperties();
            break;
            
        case 'property':
            // İlan işlemleri: /api/admin/property/{id}/{action}
            if (!isset($segments[2]) || !is_numeric($segments[2])) {
                Response::error('Geçerli emlak ID gereklidir', 400);
            }
            
            $property_id = $segments[2];
            $property_action = $segments[3] ?? null;
            
            switch ($property_action) {
                case 'toggle-status':
                    // İlan durumunu değiştir: /api/admin/property/{id}/toggle-status
                    if ($method !== 'PUT') {
                        Response::error('Bu endpoint sadece PUT metodunu destekler', 405);
                    }
                    $adminController->togglePropertyStatus($property_id);
                    break;
                    
                case 'delete':
                    // İlanı kalıcı sil: /api/admin/property/{id}/delete
                    if ($method !== 'DELETE') {
                        Response::error('Bu endpoint sadece DELETE metodunu destekler', 405);
                    }
                    $adminController->deleteProperty($property_id);
                    break;
                    
                default:
                    Response::notFound('İlan işlemi bulunamadı: ' . $property_action);
            }
            break;
            
        case 'settings':
            // Sistem ayarları: /api/admin/settings
            if ($method !== 'GET') {
                Response::error('Bu endpoint sadece GET metodunu destekler', 405);
            }
            $adminController->getSystemSettings();
            break;
            
        default:
            Response::notFound('Admin endpoint bulunamadı: ' . $action);
    }
}
?> 