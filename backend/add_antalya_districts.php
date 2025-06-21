<?php
/**
 * Antalya'daki tüm ilçeleri ekleyen script
 * Emlak-Delfino Projesi
 * ⚠️ KRITIK: Antalya ID = 5, Plaka = 07
 */

require_once 'config/database.php';

// Veritabanı bağlantısı oluştur
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Veritabanı bağlantısı başarısız!");
}

echo "=== ANTALYA İLÇELERİNİ KONTROL VE EKLEME ===\n\n";

// Antalya'nın city_id'sini bul ve doğrula
$antalya_query = "SELECT id, name, plate_code FROM cities WHERE name = 'Antalya'";
$stmt = $db->prepare($antalya_query);
$stmt->execute();
$antalya = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$antalya) {
    die("❌ Antalya ili bulunamadı!");
}

echo "🔍 ANTALYA BİLGİLERİ:\n";
echo "ID: " . $antalya['id'] . "\n";
echo "İsim: " . $antalya['name'] . "\n";
echo "Plaka: " . $antalya['plate_code'] . "\n\n";

// Kritik kontrol
if ($antalya['plate_code'] !== '07') {
    die("❌ HATA: Antalya'nın plaka kodu 07 olmalı, şu an: " . $antalya['plate_code']);
}

$antalya_id = $antalya['id'];
echo "✅ Antalya ID doğrulandı: $antalya_id\n\n";

// Antalya'daki tüm ilçeler (19 ilçe)
$antalya_districts = [
    'Aksu',
    'Alanya',
    'Akseki',
    'Demre',
    'Döşemealtı',
    'Elmalı',
    'Finike',
    'Gazipaşa',
    'Gündoğmuş',
    'İbradı',
    'Kaş',
    'Kemer',
    'Kepez',
    'Konyaaltı',
    'Korkuteli',
    'Kumluca',
    'Manavgat',
    'Muratpaşa',
    'Serik'
];

try {
    // Mevcut ilçeleri getir
    $existing_query = "SELECT name FROM districts WHERE city_id = :city_id ORDER BY name";
    $stmt = $db->prepare($existing_query);
    $stmt->bindParam(':city_id', $antalya_id, PDO::PARAM_INT);
    $stmt->execute();
    $existing_districts = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Mevcut ilçe sayısı: " . count($existing_districts) . "\n";
    echo "Toplam olması gereken ilçe sayısı: " . count($antalya_districts) . "\n\n";
    
    if (!empty($existing_districts)) {
        echo "Mevcut ilçeler:\n";
        foreach ($existing_districts as $district) {
            echo "- $district\n";
        }
        echo "\n";
    } else {
        echo "⚠️  Antalya'da henüz hiç ilçe kayıtlı değil.\n\n";
    }
    
    $added_count = 0;
    $skipped_count = 0;
    
    echo "=== ANTALYA İLÇELERİNİ EKLEME ===\n";
    foreach ($antalya_districts as $district_name) {
        if (in_array($district_name, $existing_districts)) {
            echo "⏭️  $district_name zaten mevcut\n";
            $skipped_count++;
        } else {
            // Yeni ilçe ekle - KRİTİK: city_id = 5 kullan
            $insert_query = "INSERT INTO districts (city_id, name, created_at, updated_at) 
                           VALUES (:city_id, :name, NOW(), NOW())";
            $stmt = $db->prepare($insert_query);
            $stmt->bindParam(':city_id', $antalya_id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $district_name);
            
            if ($stmt->execute()) {
                $new_id = $db->lastInsertId();
                echo "✅ $district_name eklendi (ID: $new_id, city_id: $antalya_id)\n";
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
    $stmt->bindParam(':city_id', $antalya_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Antalya'daki toplam ilçe sayısı: " . $result['total'] . "\n";
    
    if ($result['total'] == 19) {
        echo "🎉 Antalya'daki 19 ilçenin tamamı başarıyla eklendi!\n";
    } else {
        echo "ℹ️  Antalya'daki mevcut ilçe sayısı: " . $result['total'] . "\n";
    }
    
    // Eklenen ilçeleri doğrula
    echo "\n=== SON KONTROL ===\n";
    $verify_query = "SELECT d.id, d.name, d.city_id, c.name as city_name, c.plate_code 
                     FROM districts d 
                     JOIN cities c ON d.city_id = c.id 
                     WHERE d.city_id = :city_id 
                     ORDER BY d.name";
    $stmt = $db->prepare($verify_query);
    $stmt->bindParam(':city_id', $antalya_id, PDO::PARAM_INT);
    $stmt->execute();
    $final_districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Final kontrol - Antalya (ID: $antalya_id) ilçeleri:\n";
    foreach ($final_districts as $district) {
        $marker = in_array($district['name'], $antalya_districts) ? "✅" : "⚠️";
        echo "$marker {$district['name']} (District ID: {$district['id']}, City ID: {$district['city_id']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Hata oluştu: " . $e->getMessage() . "\n";
    echo "Hata detayı: " . $e->getTraceAsString() . "\n";
}

echo "\n=== İŞLEM TAMAMLANDI ===\n";
?>