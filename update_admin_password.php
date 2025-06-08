<?php
require_once 'backend/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Mevcut admin kullanıcısının şifresini güncelle
    $updateQuery = "UPDATE users SET password = :password WHERE email = 'admin@emlakdelfino.com'";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute([
        ':password' => password_hash('admin123', PASSWORD_DEFAULT)
    ]);
    
    echo "Admin kullanıcısının şifresi 'admin123' olarak güncellendi.\n";
    
    // Test login
    $loginData = [
        'email' => 'admin@emlakdelfino.com',
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
    
    echo "Login Test Response (HTTP $httpCode):\n";
    echo $response . "\n\n";
    
    if ($httpCode === 200) {
        $loginResult = json_decode($response, true);
        if (isset($loginResult['data']['token'])) {
            $token = $loginResult['data']['token'];
            echo "✅ JWT Token başarıyla alındı!\n";
            echo "Token: " . substr($token, 0, 50) . "...\n\n";
            
            // Admin dashboard testi
            echo "🔧 Admin Dashboard testi yapılıyor...\n";
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
            if ($dashboardHttpCode === 200) {
                echo "✅ Dashboard başarılı!\n";
                $dashboardData = json_decode($dashboardResponse, true);
                if (isset($dashboardData['data'])) {
                    echo "📊 İstatistikler:\n";
                    if (isset($dashboardData['data']['user_stats'])) {
                        $userStats = $dashboardData['data']['user_stats'];
                        echo "- Toplam Kullanıcı: " . $userStats['total_users'] . "\n";
                        echo "- Aktif Kullanıcı: " . $userStats['active_users'] . "\n";
                        echo "- Admin: " . $userStats['admins'] . "\n";
                        echo "- Super Admin: " . $userStats['super_admins'] . "\n";
                    }
                }
            } else {
                echo "❌ Dashboard hatası:\n";
                echo $dashboardResponse . "\n";
            }
            echo "\n";
            
            // Admin users testi
            echo "👥 Admin Users testi yapılıyor...\n";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost/emlak-delfino/backend/api/admin/users');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $usersResponse = curl_exec($ch);
            $usersHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo "Users Response (HTTP $usersHttpCode):\n";
            if ($usersHttpCode === 200) {
                echo "✅ Users listesi başarılı!\n";
                $usersData = json_decode($usersResponse, true);
                if (isset($usersData['data']['users'])) {
                    echo "📋 Kullanıcı sayısı: " . count($usersData['data']['users']) . "\n";
                }
            } else {
                echo "❌ Users listesi hatası:\n";
                echo $usersResponse . "\n";
            }
            echo "\n";
            
            // Admin settings testi
            echo "⚙️ Admin Settings testi yapılıyor...\n";
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
            if ($settingsHttpCode === 200) {
                echo "✅ Settings başarılı!\n";
            } else {
                echo "❌ Settings hatası:\n";
                echo $settingsResponse . "\n";
            }
        }
    } else {
        echo "❌ Login başarısız!\n";
    }
    
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?> 