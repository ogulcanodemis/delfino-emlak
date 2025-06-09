<?php
/**
 * Authentication Controller
 * Emlak-Delfino Projesi
 */

require_once '../models/User.php';
require_once '../utils/JWT.php';

class AuthController {
    private $db;
    private $user;

    public function __construct($database) {
        $this->db = $database;
        $this->user = new User($this->db);
    }

    /**
     * Kullanıcı kaydı
     */
    public function register() {
        // POST verilerini al
        $data = json_decode(file_get_contents("php://input"), true);

        // Gerekli alanları kontrol et
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            Response::validationError(['message' => 'Ad, e-posta ve şifre alanları zorunludur']);
        }

        // Şifre onayını kontrol et
        if (isset($data['password_confirmation']) && $data['password'] !== $data['password_confirmation']) {
            Response::validationError(['message' => 'Şifreler eşleşmiyor']);
        }

        // E-posta formatını kontrol et
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::validationError(['message' => 'Geçersiz e-posta formatı']);
        }

        // E-posta benzersizliğini kontrol et
        if ($this->user->emailExists($data['email'])) {
            Response::validationError(['message' => 'Bu e-posta adresi zaten kullanılıyor']);
        }

        // Şifre uzunluğunu kontrol et
        if (strlen($data['password']) < 6) {
            Response::validationError(['message' => 'Şifre en az 6 karakter olmalıdır']);
        }

        // Kullanıcı verilerini ayarla
        $this->user->name = $data['name'];
        $this->user->email = $data['email'];
        $this->user->password = $data['password'];
        $this->user->phone = $data['phone'] ?? null;
        $this->user->address = $data['address'] ?? null;
        $this->user->city_id = $data['city_id'] ?? null;
        $this->user->district_id = $data['district_id'] ?? null;
        $this->user->neighborhood_id = $data['neighborhood_id'] ?? null;
        $this->user->role_id = 1; // Varsayılan: Kayıtlı Kullanıcı
        $this->user->status = 1; // Aktif

        // Kullanıcıyı oluştur
        if ($this->user->create()) {
            // JWT token oluştur
            $token_payload = [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'role_id' => $this->user->role_id,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60) // 7 gün
            ];

            $token = JWT::encode($token_payload);

            // Kullanıcı bilgilerini al
            $user_data = $this->user->findById($this->user->id);
            unset($user_data['password']); // Şifreyi çıkar

            Response::success([
                'user' => $user_data,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => date('Y-m-d H:i:s', $token_payload['exp'])
            ], 'Kullanıcı başarıyla kaydedildi', 201);
        } else {
            Response::error('Kullanıcı kaydı sırasında bir hata oluştu', 500);
        }
    }

    /**
     * Kullanıcı girişi
     */
    public function login() {
        // POST verilerini al
        $data = json_decode(file_get_contents("php://input"), true);

        // Gerekli alanları kontrol et
        if (empty($data['email']) || empty($data['password'])) {
            Response::validationError(['message' => 'E-posta ve şifre alanları zorunludur']);
        }

        // Kullanıcıyı bul
        $user_data = $this->user->findByEmail($data['email']);

        if (!$user_data) {
            Response::error('Geçersiz e-posta veya şifre', 401);
        }

        // Şifreyi doğrula
        if (!$this->user->verifyPassword($data['password'])) {
            Response::error('Geçersiz e-posta veya şifre', 401);
        }

        // Son giriş zamanını güncelle
        $this->user->updateLastLogin();

        // JWT token oluştur
        $token_payload = [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'role_id' => $this->user->role_id,
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60) // 7 gün
        ];

        $token = JWT::encode($token_payload);

        // Şifreyi çıkar
        unset($user_data['password']);

        Response::success([
            'user' => $user_data,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => date('Y-m-d H:i:s', $token_payload['exp'])
        ], 'Giriş başarılı');
    }

    /**
     * Kullanıcı bilgilerini getir
     */
    public function me() {
        // Authorization header'ını kontrol et
        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$auth_header || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            Response::unauthorized('Token bulunamadı');
        }

        $token = $matches[1];

        // Token'ı doğrula
        $payload = JWT::decode($token);
        if (!$payload) {
            Response::unauthorized('Geçersiz token');
        }

        // Kullanıcı bilgilerini al
        $user_data = $this->user->findById($payload['user_id']);
        if (!$user_data) {
            Response::unauthorized('Kullanıcı bulunamadı');
        }

        // Şifreyi çıkar
        unset($user_data['password']);

        Response::success(['user' => $user_data]);
    }

    /**
     * Profil güncelleme
     */
    public function updateProfile() {
        // Authorization header'ını kontrol et
        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$auth_header || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            Response::unauthorized('Token bulunamadı');
        }

        $token = $matches[1];

        // Token'ı doğrula
        $payload = JWT::decode($token);
        if (!$payload) {
            Response::unauthorized('Geçersiz token');
        }

        // POST verilerini al
        $data = json_decode(file_get_contents("php://input"), true);

        // Kullanıcıyı bul
        $user_data = $this->user->findById($payload['user_id']);
        if (!$user_data) {
            Response::unauthorized('Kullanıcı bulunamadı');
        }

        // Güncellenecek verileri ayarla
        $this->user->id = $payload['user_id'];
        $this->user->name = $data['name'] ?? $user_data['name'];
        $this->user->phone = $data['phone'] ?? $user_data['phone'];
        $this->user->address = $data['address'] ?? $user_data['address'];
        $this->user->bio = $data['bio'] ?? $user_data['bio'];
        $this->user->company = $data['company'] ?? $user_data['company'];
        $this->user->website = $data['website'] ?? $user_data['website'];
        $this->user->city_id = $data['city_id'] ?? $user_data['city_id'];
        $this->user->district_id = $data['district_id'] ?? $user_data['district_id'];
        $this->user->neighborhood_id = $data['neighborhood_id'] ?? $user_data['neighborhood_id'];

        // Şifre güncellemesi varsa
        if (!empty($data['password'])) {
            // Mevcut şifreyi kontrol et
            if (empty($data['current_password'])) {
                Response::validationError(['message' => 'Mevcut şifre gereklidir']);
            }

            if (!password_verify($data['current_password'], $user_data['password'])) {
                Response::validationError(['message' => 'Mevcut şifre yanlış']);
            }

            // Şifre onayını kontrol et
            if (isset($data['password_confirmation']) && $data['password'] !== $data['password_confirmation']) {
                Response::validationError(['message' => 'Yeni şifreler eşleşmiyor']);
            }

            // Şifre uzunluğunu kontrol et
            if (strlen($data['password']) < 6) {
                Response::validationError(['message' => 'Şifre en az 6 karakter olmalıdır']);
            }

            // Şifreyi güncelle
            if (!$this->user->updatePassword($data['password'])) {
                Response::error('Şifre güncellenirken bir hata oluştu', 500);
            }
        }

        // Profil bilgilerini güncelle
        if ($this->user->update()) {
            // Güncellenmiş kullanıcı bilgilerini al
            $updated_user = $this->user->findById($payload['user_id']);
            unset($updated_user['password']);

            Response::success(['user' => $updated_user], 'Profil başarıyla güncellendi');
        } else {
            Response::error('Profil güncellenirken bir hata oluştu', 500);
        }
    }

    /**
     * Çıkış (logout)
     */
    public function logout() {
        // Basit logout - gerçek uygulamada token blacklist yapılabilir
        Response::success(null, 'Çıkış başarılı');
    }

    /**
     * Şifre sıfırlama isteği
     */
    public function forgotPassword() {
        // POST verilerini al
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['email'])) {
            Response::validationError(['message' => 'E-posta adresi gereklidir']);
        }

        // Kullanıcıyı bul
        $user_data = $this->user->findByEmail($data['email']);
        if (!$user_data) {
            // Güvenlik için her durumda başarılı mesaj döndür
            Response::success(null, 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi');
        }

        // TODO: E-posta gönderme işlemi burada yapılacak
        // Şimdilik sadece başarılı mesaj döndürüyoruz
        Response::success(null, 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi');
    }
}
?> 