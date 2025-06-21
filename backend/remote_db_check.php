<?php
/**
 * Remote veritabanı bağlantı test script
 */

echo "=== REMOTE VERİTABANI BAĞLANTI TESTİ ===\n";

// Farklı host seçeneklerini test et
$hosts = [
    'localhost',
    '46.202.156.206',
    'bkyatirim.com',
    'mysql.bkyatirim.com',
    'db.bkyatirim.com'
];

$db_name = 'u389707721_bkyatirim';
$username = 'u389707721_bkdb';
$password = '$iTxfq%x2B;4GJt';
$charset = 'utf8mb4';

foreach ($hosts as $host) {
    echo "\n--- Host: $host ---\n";
    
    try {
        $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 10
        ];

        $conn = new PDO($dsn, $username, $password, $options);
        
        echo "✅ Bağlantı başarılı!\n";
        echo "Server Version: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
        
        // Tabloları kontrol et
        $stmt = $conn->prepare("SHOW TABLES");
        $stmt->execute();
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Tablolar: " . implode(", ", $tables) . "\n";
        
        // Cities tablosu var mı kontrol et
        if (in_array('cities', $tables)) {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cities");
            $stmt->execute();
            $result = $stmt->fetch();
            echo "Cities tablosu kayıt sayısı: " . $result['count'] . "\n";
        }
        
        // Districts tablosu var mı kontrol et
        if (in_array('districts', $tables)) {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM districts");
            $stmt->execute();
            $result = $stmt->fetch();
            echo "Districts tablosu kayıt sayısı: " . $result['count'] . "\n";
        }
        
        $conn = null;
        break; // Başarılı bağlantı bulundu, döngüden çık
        
    } catch (PDOException $e) {
        echo "❌ Bağlantı hatası: " . $e->getMessage() . "\n";
    }
}

echo "\n=== TEST TAMAMLANDI ===\n";
?>