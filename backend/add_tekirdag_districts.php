<?php
/**
 * Tekirdağ'daki ilçeleri ekleyen script
 * Emlak-Delfino Projesi
 * ⚠️ KRITIK: Tekirdağ ID = 24, Plaka = 59
 */

require_once 'config/database.php';

// Veritabanı bağlantısı oluştur
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Veritabanı bağlantısı başarısız!");
}

echo "=== TEKİRDAĞ İLÇELERİNİ KONTROL VE EKLEME ===\n\n";

// Tekirdağ'ın city_id'sini bul ve doğrula
$tekirdag_query = "SELECT id, name, plate_code FROM cities WHERE name = 'Tekirdağ'";
$stmt = $db->prepare($tekirdag_query);
$stmt->execute();
$tekirdag = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tekirdag) {
    die("❌ Tekirdağ ili bulunamadı!");
}

echo "🔍 TEKİRDAĞ BİLGİLERİ:\n";
echo "ID: " . $tekirdag['id'] . "\n";
echo "İsim: " . $tekirdag['name'] . "\n";
echo "Plaka: " . $tekirdag['plate_code'] . "\n\n";

// Kritik kontrol
if ($tekirdag['plate_code'] !== '59') {
    die("❌ HATA: Tekirdağ'ın plaka kodu 59 olmalı, şu an: " . $tekirdag['plate_code']);
}

$tekirdag_id = $tekirdag['id'];
echo "✅ Tekirdağ ID doğrulandı: $tekirdag_id\n\n";

// Tekirdağ'daki tüm ilçeler (11 ilçe)
$tekirdag_districts = [
    'Çerkezköy',
    'Çorlu', 
    'Ergene',
    'Hayrabolu',
    'Kapaklı',
    'Malkara',
    'Marmaraereğlisi',
    'Muratlı',
    'Saray',
    'Süleymanpaşa',
    'Şarköy'
];

try {
    // Mevcut ilçeleri getir
    $existing_query = "SELECT name FROM districts WHERE city_id = :city_id ORDER BY name";
    $stmt = $db->prepare($existing_query);
    $stmt->bindParam(':city_id', $tekirdag_id, PDO::PARAM_INT);
    $stmt->execute();
    $existing_districts = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Mevcut ilçe sayısı: " . count($existing_districts) . "\n";
    echo "Toplam olması gereken ilçe sayısı: " . count($tekirdag_districts) . "\n\n";
    
    if (!empty($existing_districts)) {
        echo "Mevcut ilçeler:\n";
        foreach ($existing_districts as $district) {
            echo "- $district\n";
        }
        echo "\n";
    } else {
        echo "⚠️  Tekirdağ'da henüz hiç ilçe kayıtlı değil.\n\n";
    }
    
    $added_count = 0;
    $skipped_count = 0;
    
    echo "=== TEKİRDAĞ İLÇELERİNİ EKLEME ===\n";
    foreach ($tekirdag_districts as $district_name) {
        if (in_array($district_name, $existing_districts)) {
            echo "⏭️  $district_name zaten mevcut\n";
            $skipped_count++;
        } else {
            // Yeni ilçe ekle - KRİTİK: city_id = 24 kullan
            $insert_query = "INSERT INTO districts (city_id, name, created_at, updated_at) 
                           VALUES (:city_id, :name, NOW(), NOW())";
            $stmt = $db->prepare($insert_query);
            $stmt->bindParam(':city_id', $tekirdag_id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $district_name);
            
            if ($stmt->execute()) {
                $new_id = $db->lastInsertId();
                echo "✅ $district_name eklendi (ID: $new_id, city_id: $tekirdag_id)\n";
                $added_count++;
            } else {
                echo "❌ $district_name eklenemedi\n";
                print_r($stmt->errorInfo());
            }
        }
    }
    
    echo "\n=== ÖZET ===\n";
    echo "Yeni eklenen ilçe sayısı: $added_count\n";
    echo "Zaten mevcut ilçe sayısı: $skipped_count\n";
    
    // Final kontrol - KRITIK: Doğru city_id ile kontrol et
    $final_query = "SELECT COUNT(*) as total FROM districts WHERE city_id = :city_id";
    $stmt = $db->prepare($final_query);
    $stmt->bindParam(':city_id', $tekirdag_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Tekirdağ'daki toplam ilçe sayısı: " . $result['total'] . "\n";
    
    if ($result['total'] == 11) {
        echo "🎉 Tekirdağ'daki 11 ilçenin tamamı başarıyla eklendi!\n";
    } else {
        echo "⚠️  Tekirdağ'daki ilçe sayısı 11 değil. Kontrol edilmesi gerekiyor.\n";
    }
    
    // Eklenen ilçeleri doğrula
    echo "\n=== SON KONTROL ===\n";
    $verify_query = "SELECT d.id, d.name, d.city_id, c.name as city_name, c.plate_code 
                     FROM districts d 
                     JOIN cities c ON d.city_id = c.id 
                     WHERE d.city_id = :city_id 
                     ORDER BY d.name";
    $stmt = $db->prepare($verify_query);
    $stmt->bindParam(':city_id', $tekirdag_id, PDO::PARAM_INT);
    $stmt->execute();
    $final_districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Final kontrol - Tekirdağ (ID: $tekirdag_id) ilçeleri:\n";
    foreach ($final_districts as $district) {
        echo "- {$district['name']} (District ID: {$district['id']}, City ID: {$district['city_id']}, İl: {$district['city_name']}, Plaka: {$district['plate_code']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Hata oluştu: " . $e->getMessage() . "\n";
    echo "Hata detayı: " . $e->getTraceAsString() . "\n";
}

echo "\n=== İŞLEM TAMAMLANDI ===\n";
?>