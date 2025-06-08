<?php
require_once 'backend/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "🔍 ADMIN KULLANICI KONTROLÜ\n";
    echo "===========================\n\n";
    
    // Önce users tablosunun yapısını kontrol et
    echo "📋 Users tablosu yapısı:\n";
    $descQuery = "DESCRIBE users";
    $descStmt = $db->prepare($descQuery);
    $descStmt->execute();
    $columns = $descStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "   {$column['Field']} - {$column['Type']}\n";
    }
    echo "\n";
    
    // Tüm kullanıcıları listele
    echo "👥 Tüm kullanıcılar:\n";
    $query = "SELECT * FROM users ORDER BY role_id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "❌ Hiç kullanıcı bulunamadı!\n\n";
    } else {
        foreach ($users as $user) {
            echo "   ID: {$user['id']}\n";
            if (isset($user['name'])) echo "   İsim: {$user['name']}\n";
            if (isset($user['first_name'])) echo "   Ad: {$user['first_name']}\n";
            if (isset($user['last_name'])) echo "   Soyad: {$user['last_name']}\n";
            echo "   Email: {$user['email']}\n";
            echo "   Rol ID: {$user['role_id']}\n";
            echo "   Durum: " . ($user['status'] ? 'Aktif' : 'Pasif') . "\n";
            if (isset($user['created_at'])) echo "   Oluşturulma: {$user['created_at']}\n";
            echo "   ---\n";
        }
    }
    
    // Admin kullanıcıları kontrol et
    echo "🔑 Admin kullanıcıları (role_id 3 veya 4):\n";
    $adminQuery = "SELECT * FROM users WHERE role_id IN (3, 4)";
    $adminStmt = $db->prepare($adminQuery);
    $adminStmt->execute();
    $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($admins)) {
        echo "❌ Hiç admin kullanıcısı bulunamadı!\n";
        
        // emlakci@emlakdelfino.com kullanıcısını admin yap
        echo "\n🔧 'emlakci@emlakdelfino.com' kullanıcısını admin yapıyoruz...\n";
        $updateQuery = "UPDATE users SET role_id = 4, password = :password WHERE email = 'emlakci@emlakdelfino.com'";
        $updateStmt = $db->prepare($updateQuery);
        $result = $updateStmt->execute([
            ':password' => password_hash('password', PASSWORD_DEFAULT)
        ]);
        
        if ($result && $updateStmt->rowCount() > 0) {
            echo "✅ emlakci@emlakdelfino.com kullanıcısı super admin yapıldı!\n";
            echo "   Email: emlakci@emlakdelfino.com\n";
            echo "   Şifre: password\n\n";
        } else {
            echo "❌ Kullanıcı bulunamadı veya güncellenemedi.\n";
            
            // Yeni admin oluştur
            echo "🔧 Yeni admin oluşturuluyor...\n";
            $nameColumn = isset($columns[0]) && $columns[0]['Field'] === 'name' ? 'name' : 'first_name';
            
            if ($nameColumn === 'name') {
                $insertQuery = "INSERT INTO users (name, email, password, role_id, status, created_at) 
                                VALUES (:name, :email, :password, :role_id, 1, NOW())";
                $insertData = [
                    ':name' => 'Super Admin',
                    ':email' => 'admin@emlak-delfino.com',
                    ':password' => password_hash('admin123', PASSWORD_DEFAULT),
                    ':role_id' => 4
                ];
            } else {
                $insertQuery = "INSERT INTO users (first_name, last_name, email, password, role_id, status, created_at) 
                                VALUES (:first_name, :last_name, :email, :password, :role_id, 1, NOW())";
                $insertData = [
                    ':first_name' => 'Super',
                    ':last_name' => 'Admin',
                    ':email' => 'admin@emlak-delfino.com',
                    ':password' => password_hash('admin123', PASSWORD_DEFAULT),
                    ':role_id' => 4
                ];
            }
            
            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->execute($insertData);
            
            echo "✅ Yeni super admin oluşturuldu!\n";
            echo "   Email: admin@emlak-delfino.com\n";
            echo "   Şifre: admin123\n\n";
        }
        
    } else {
        echo "✅ Admin kullanıcıları bulundu:\n";
        foreach ($admins as $admin) {
            $roleName = $admin['role_id'] == 4 ? 'Super Admin' : 'Admin';
            echo "   ID: {$admin['id']}\n";
            if (isset($admin['name'])) echo "   İsim: {$admin['name']}\n";
            echo "   Email: {$admin['email']}\n";
            echo "   Rol: $roleName (ID: {$admin['role_id']})\n";
            echo "   ---\n";
        }
    }
    
    // Test girişi yap - emlakci@emlakdelfino.com ile
    echo "\n🧪 Giriş testi yapılıyor (emlakci@emlakdelfino.com)...\n";
    
    $loginData = [
        'email' => 'emlakci@emlakdelfino.com',
        'password' => 'password'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/emlak-delfino/backend/api/auth/login');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Giriş Sonucu (HTTP $httpCode):\n";
    echo $response . "\n\n";
    
    if ($httpCode === 200) {
        echo "✅ Giriş başarılı! API tester artık çalışabilir.\n";
    } else {
        echo "❌ Giriş başarısız. Auth endpoint'ini kontrol edelim.\n";
        
        // Auth dosyasının varlığını kontrol et
        echo "\n🔍 Auth dosyaları kontrolü...\n";
        $authFiles = [
            'backend/api/auth/login.php',
            'backend/controllers/AuthController.php',
            'backend/api/index.php'
        ];
        
        foreach ($authFiles as $file) {
            if (file_exists($file)) {
                echo "✅ $file - Mevcut\n";
            } else {
                echo "❌ $file - Bulunamadı\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?> 