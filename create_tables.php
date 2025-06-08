<?php
require_once 'backend/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "🗄️ VERİTABANI TABLOLARI OLUŞTURULUYOR\n";
    echo "====================================\n\n";
    
    // Notifications tablosu
    echo "📋 Notifications tablosu oluşturuluyor...\n";
    $notificationsSQL = "
    CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('property_approved', 'property_rejected', 'role_request', 'system', 'general', 'property_update', 'user_message') DEFAULT 'general',
        related_id INT NULL,
        related_type VARCHAR(50) NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_type (type),
        INDEX idx_is_read (is_read),
        INDEX idx_created_at (created_at),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($notificationsSQL);
    echo "✅ Notifications tablosu oluşturuldu\n\n";
    
    // Contacts tablosu
    echo "📋 Contacts tablosu oluşturuluyor...\n";
    $contactsSQL = "
    CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(191) NOT NULL,
        phone VARCHAR(20) NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        contact_type ENUM('general', 'support', 'property_inquiry', 'partnership', 'complaint') DEFAULT 'general',
        property_id INT NULL,
        user_id INT NULL,
        status ENUM('pending', 'in_progress', 'resolved', 'closed') DEFAULT 'pending',
        admin_notes TEXT NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        is_spam BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_contact_type (contact_type),
        INDEX idx_status (status),
        INDEX idx_property_id (property_id),
        INDEX idx_user_id (user_id),
        INDEX idx_created_at (created_at),
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($contactsSQL);
    echo "✅ Contacts tablosu oluşturuldu\n\n";
    
    // Property types tablosunun var olup olmadığını kontrol et
    echo "📋 Property types tablosu kontrol ediliyor...\n";
    $checkPropertyTypes = "SHOW TABLES LIKE 'property_types'";
    $result = $db->query($checkPropertyTypes);
    
    if ($result->rowCount() == 0) {
        echo "📋 Property types tablosu oluşturuluyor...\n";
        $propertyTypesSQL = "
        CREATE TABLE property_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT NULL,
            icon VARCHAR(50) NULL,
            is_active BOOLEAN DEFAULT TRUE,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_is_active (is_active),
            INDEX idx_sort_order (sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $db->exec($propertyTypesSQL);
        
        // Varsayılan emlak tiplerini ekle
        $defaultTypes = [
            ['name' => 'Daire', 'description' => 'Apartman dairesi', 'icon' => 'apartment', 'sort_order' => 1],
            ['name' => 'Villa', 'description' => 'Müstakil villa', 'icon' => 'villa', 'sort_order' => 2],
            ['name' => 'Müstakil Ev', 'description' => 'Müstakil ev', 'icon' => 'house', 'sort_order' => 3],
            ['name' => 'Dubleks', 'description' => 'İki katlı daire', 'icon' => 'duplex', 'sort_order' => 4],
            ['name' => 'Penthouse', 'description' => 'Çatı katı daire', 'icon' => 'penthouse', 'sort_order' => 5],
            ['name' => 'Stüdyo', 'description' => 'Tek oda stüdyo', 'icon' => 'studio', 'sort_order' => 6],
            ['name' => 'Ofis', 'description' => 'Ticari ofis', 'icon' => 'office', 'sort_order' => 7],
            ['name' => 'Dükkan', 'description' => 'Ticari dükkan', 'icon' => 'shop', 'sort_order' => 8],
            ['name' => 'Arsa', 'description' => 'İnşaat arsası', 'icon' => 'land', 'sort_order' => 9],
            ['name' => 'Depo', 'description' => 'Depo/Antrepo', 'icon' => 'warehouse', 'sort_order' => 10]
        ];
        
        $insertSQL = "INSERT INTO property_types (name, description, icon, sort_order) VALUES (?, ?, ?, ?)";
        $insertStmt = $db->prepare($insertSQL);
        
        foreach ($defaultTypes as $type) {
            $insertStmt->execute([$type['name'], $type['description'], $type['icon'], $type['sort_order']]);
        }
        
        echo "✅ Property types tablosu oluşturuldu ve varsayılan veriler eklendi\n\n";
    } else {
        echo "✅ Property types tablosu zaten mevcut\n\n";
    }
    
    // Mevcut tabloları listele
    echo "📋 Mevcut tablolar:\n";
    $tablesQuery = "SHOW TABLES";
    $tablesResult = $db->query($tablesQuery);
    $tables = $tablesResult->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "   ✓ $table\n";
    }
    
    echo "\n🎉 Tüm tablolar başarıyla oluşturuldu!\n";
    
    // Test verileri ekle
    echo "\n📊 Test verileri ekleniyor...\n";
    
    // Test bildirimi ekle
    $testNotificationSQL = "
    INSERT IGNORE INTO notifications (user_id, title, message, type) 
    VALUES (1, 'Hoş Geldiniz!', 'Emlak-Delfino sistemine hoş geldiniz. Yeni özelliklerimizi keşfedin.', 'system')
    ";
    $db->exec($testNotificationSQL);
    
    // Test iletişim mesajı ekle
    $testContactSQL = "
    INSERT IGNORE INTO contacts (name, email, subject, message, contact_type) 
    VALUES ('Test Kullanıcı', 'test@example.com', 'Test Mesajı', 'Bu bir test mesajıdır.', 'general')
    ";
    $db->exec($testContactSQL);
    
    echo "✅ Test verileri eklendi\n";
    echo "\n🚀 Sistem hazır! API tester'ı tekrar çalıştırabilirsiniz.\n";
    
} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?> 