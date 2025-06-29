<?php
// Property-User JOIN testi
header('Content-Type: text/html; charset=utf-8');

try {
    // Basit PDO bağlantısı
    $host = 'localhost';
    $dbname = 'u389707721_bkyatirim'; // Canlı veritabanı adı
    $username = 'u389707721_bkdb';
    $password = 'Your_Password_Here'; // Şifreyi güncelleyin
    
    echo "<h2>Property-User JOIN Test</h2>";
    
    // Test sorgusu
    $query = "SELECT p.id, p.title, p.user_id, 
                     u.name as user_name, 
                     u.profile_image as user_profile_image
              FROM properties p 
              LEFT JOIN users u ON p.user_id = u.id 
              WHERE p.id = 70";
    
    echo "<h3>SQL Query:</h3>";
    echo "<pre>" . htmlspecialchars($query) . "</pre>";
    
    // Bu test sadece query'yi göstermek için
    echo "<h3>❗ Bu dosyayı tarayıcıda çalıştırmayın - sadece SQL'i kopyalayın</h3>";
    echo "<p>Bu SQL'i phpMyAdmin'de çalıştırın ve sonucu paylaşın:</p>";
    echo "<code style='background: #f0f0f0; padding: 10px; display: block;'>";
    echo htmlspecialchars($query);
    echo "</code>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!-- Veritabanı şifresi bilgisi olmadığı için bu test çalışmayacak -->
<!-- Lütfen yukarıdaki SQL'i phpMyAdmin'de manuel çalıştırın -->