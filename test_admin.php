<?php
require_once 'backend/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Admin kullanıcısı var mı kontrol et
    $checkQuery = "SELECT id, email, role_id FROM users WHERE role_id IN (3, 4) LIMIT 1";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute();
    $existingAdmin = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingAdmin) {
        echo "Mevcut admin kullanıcısı bulundu:\n";
        echo "ID: " . $existingAdmin['id'] . "\n";
        echo "Email: " . $existingAdmin['email'] . "\n";
        echo "Role ID: " . $existingAdmin['role_id'] . "\n\n";
    } else {
        echo "Admin kullanıcısı bulunamadı. Yeni admin oluşturuluyor...\n";
        
        // Yeni admin kullanıcısı oluştur
        $insertQuery = "INSERT INTO users (name, email, password, role_id, status, email_verified_at, created_at, updated_at) 
                        VALUES (:name, :email, :password, :role_id, 1, NOW(), NOW(), NOW())";
        
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute([
            ':name' => 'Super Admin',
            ':email' => 'admin@emlak-delfino.com',
            ':password' => password_hash('admin123', PASSWORD_DEFAULT),
            ':role_id' => 4
        ]);
        
        echo "Yeni super admin kullanıcısı oluşturuldu:\n";
        echo "Email: admin@emlak-delfino.com\n";
        echo "Password: admin123\n";
        echo "Role ID: 4 (super_admin)\n\n";
    }
    
    // Admin login testi
    echo "Admin login testi yapılıyor...\n";
    
    $loginData = [
        'email' => $existingAdmin ? $existingAdmin['email'] : 'admin@emlak-delfino.com',
        'password' => 'admin123'
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
    
    echo "Login Response (HTTP $httpCode):\n";
    echo $response . "\n\n";
    
    if ($httpCode === 200) {
        $loginResult = json_decode($response, true);
        if (isset($loginResult['data']['token'])) {
            $token = $loginResult['data']['token'];
            echo "JWT Token alındı. Admin dashboard testi yapılıyor...\n";
            
            // Admin dashboard testi
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost/emlak-delfino/backend/api/admin/dashboard');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $dashboardResponse = curl_exec($ch);
            $dashboardHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo "Dashboard Response (HTTP $dashboardHttpCode):\n";
            echo $dashboardResponse . "\n\n";
            
            // Admin settings testi
            echo "Admin settings testi yapılıyor...\n";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost/emlak-delfino/backend/api/admin/settings');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $settingsResponse = curl_exec($ch);
            $settingsHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo "Settings Response (HTTP $settingsHttpCode):\n";
            echo $settingsResponse . "\n\n";
        }
    }
    
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?> 