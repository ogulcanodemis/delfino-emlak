<?php
// Test Database Connection
// Bu dosyayı canlı sunucuya yükle ve çalıştır: https://bkyatirim.com/backend/test_db_connection.php

echo "<h2>Database Connection Test</h2>";

// Gerçek sunucu bilgileriyle test et
$test_configs = [
    [
        'host' => 'localhost',
        'db_name' => 'u389707721_bkyatirim',
        'username' => 'u389707721_bkdb',
        'password' => '$iTxfq%x2B;4GJt'
    ]
];

foreach ($test_configs as $index => $config) {
    echo "<h3>Test " . ($index + 1) . ":</h3>";
    echo "Host: " . $config['host'] . "<br>";
    echo "Database: " . $config['db_name'] . "<br>";
    echo "Username: " . $config['username'] . "<br>";
    
    try {
        $dsn = "mysql:host=" . $config['host'] . ";dbname=" . $config['db_name'] . ";charset=utf8mb4";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        
        echo "<span style='color: green;'>✅ BAŞARILI! Bağlantı kuruldu.</span><br>";
        echo "MySQL Version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "<br>";
        
        // Test query
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Tablolar (" . count($tables) . " adet): " . implode(", ", $tables) . "<br>";
        
    } catch (PDOException $e) {
        echo "<span style='color: red;'>❌ HATA: " . $e->getMessage() . "</span><br>";
    }
    
    echo "<hr>";
}

echo "<h3>Sunucu Bilgileri:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Server Name: " . $_SERVER['SERVER_NAME'] . "<br>";
?>