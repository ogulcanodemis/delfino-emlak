<?php
/**
 * Migration Script: Users tablosuna profile_image alanı ekleme
 * Canlı sunucuda https://bkyatirim.com/migrate_profile_image.php olarak çalıştırın
 */

require_once 'backend/config/database.php';

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Profil Resmi Migration Script</h2>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Veritabanı bağlantısı kurulamadı");
    }
    
    echo "<p>✅ Veritabanı bağlantısı başarılı</p>";
    
    // Önce alan var mı kontrol et
    $checkQuery = "SELECT COLUMN_NAME 
                   FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'users' 
                     AND COLUMN_NAME = 'profile_image'";
    
    $stmt = $db->prepare($checkQuery);
    $stmt->execute();
    $columnExists = $stmt->rowCount() > 0;
    
    if ($columnExists) {
        echo "<p>⚠️ profile_image alanı zaten mevcut</p>";
        
        // Mevcut veriyi kontrol et
        $testQuery = "SELECT id, name, profile_image FROM users WHERE profile_image IS NOT NULL LIMIT 5";
        $stmt = $db->prepare($testQuery);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Profil resmi olan kullanıcılar:</strong></p>";
        if (empty($users)) {
            echo "<p>Henüz profil resmi yüklenmiş kullanıcı yok</p>";
        } else {
            echo "<ul>";
            foreach ($users as $user) {
                echo "<li>ID: {$user['id']}, İsim: {$user['name']}, Profil Resmi: {$user['profile_image']}</li>";
            }
            echo "</ul>";
        }
        
    } else {
        echo "<p>🔧 profile_image alanı bulunamadı, ekleniyor...</p>";
        
        $alterQuery = "ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) NULL AFTER status";
        $stmt = $db->prepare($alterQuery);
        
        if ($stmt->execute()) {
            echo "<p>✅ profile_image alanı başarıyla eklendi!</p>";
        } else {
            throw new Exception("Alan eklenirken hata oluştu");
        }
    }
    
    // Tablo yapısını göster
    echo "<h3>Users Tablosu Yapısı:</h3>";
    $describeQuery = "DESCRIBE users";
    $stmt = $db->prepare($describeQuery);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        $highlight = ($column['Field'] === 'profile_image') ? 'style="background-color: #ffffcc;"' : '';
        echo "<tr {$highlight}>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Migration Tamamlandı! 🎉</h3>";
    echo "<p>Artık kullanıcılar profil resmi yükleyebilir ve ilan detaylarında görüntülenebilir.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Hata: " . $e->getMessage() . "</p>";
    echo "<p>Veritabanı bağlantı bilgilerini kontrol edin.</p>";
}

echo "<hr>";
echo "<p><small>Bu dosyayı migration tamamlandıktan sonra silebilirsiniz.</small></p>";
?>