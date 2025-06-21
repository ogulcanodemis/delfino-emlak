<?php
/**
 * Ankara'daki tüm ilçeleri ekleyen script
 * Emlak-Delfino Projesi
 * ⚠️ KRITIK: Ankara ID = 2, Plaka = 06
 */

require_once 'config/database.php';

// Veritabanı bağlantısı oluştur
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Veritabanı bağlantısı başarısız!");
}

echo "=== ANKARA İLÇELERİNİ KONTROL VE EKLEME ===\n\n";

// Ankara'nın city_id'sini bul ve doğrula
$ankara_query = "SELECT id, name, plate_code FROM cities WHERE name = 'Ankara'";
$stmt = $db->prepare($ankara_query);
$stmt->execute();
$ankara = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ankara) {
    die("❌ Ankara ili bulunamadı!");
}

echo "🔍 ANKARA BİLGİLERİ:\n";
echo "ID: " . $ankara['id'] . "\n";
echo "İsim: " . $ankara['name'] . "\n";
echo "Plaka: " . $ankara['plate_code'] . "\n\n";

// Kritik kontrol
if ($ankara['plate_code'] !== '06') {
    die("❌ HATA: Ankara'nın plaka kodu 06 olmalı, şu an: " . $ankara['plate_code']);
}

$ankara_id = $ankara['id'];
echo "✅ Ankara ID doğrulandı: $ankara_id\n\n";

// Ankara'daki tüm ilçeler (25 ilçe)
$ankara_districts = [
    'Akyurt',
    'Altındağ',
    'Ayaş',
    'Bala',
    'Beypazarı',
    'Çamlıdere',
    'Çankaya',
    'Çubuk',
    'Elmadağ',
    'Etimesgut',
    'Evren',
    'Gölbaşı',
    'Güdül',
    'Haymana',
    'Kalecik',
    'Kızılcahamam',
    'Keçiören',
    'Mamak',
    'Nallıhan',
    'Polatlı',
    'Pursaklar',
    'Sincan',
    'Şereflikoçhisar',
    'Yenimahalle',
    'Kazan'
];

try {
    // Mevcut ilçeleri getir
    $existing_query = "SELECT name FROM districts WHERE city_id = :city_id ORDER BY name";
    $stmt = $db->prepare($existing_query);
    $stmt->bindParam(':city_id', $ankara_id, PDO::PARAM_INT);
    $stmt->execute();
    $existing_districts = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Mevcut ilçe sayısı: " . count($existing_districts) . "\n";
    echo "Toplam olması gereken ilçe sayısı: " . count($ankara_districts) . "\n\n";
    
    if (!empty($existing_districts)) {
        echo "Mevcut ilçeler:\n";
        foreach ($existing_districts as $district) {
            echo "- $district\n";
        }
        echo "\n";
    }
    
    $added_count = 0;
    $skipped_count = 0;
    
    echo "=== ANKARA İLÇELERİNİ EKLEME ===\n";
    foreach ($ankara_districts as $district_name) {
        if (in_array($district_name, $existing_districts)) {
            echo "⏭️  $district_name zaten mevcut\n";
            $skipped_count++;
        } else {
            // Yeni ilçe ekle - KRİTİK: city_id = 2 kullan
            $insert_query = "INSERT INTO districts (city_id, name, created_at, updated_at) 
                           VALUES (:city_id, :name, NOW(), NOW())";
            $stmt = $db->prepare($insert_query);
            $stmt->bindParam(':city_id', $ankara_id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $district_name);
            
            if ($stmt->execute()) {
                $new_id = $db->lastInsertId();
                echo "✅ $district_name eklendi (ID: $new_id, city_id: $ankara_id)\n";
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
    $stmt->bindParam(':city_id', $ankara_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Ankara'daki toplam ilçe sayısı: " . $result['total'] . "\n";
    
    if ($result['total'] == 25) {
        echo "🎉 Ankara'daki 25 ilçenin tamamı başarıyla eklendi!\n";
    } else {
        echo "ℹ️  Ankara'daki mevcut ilçe sayısı: " . $result['total'] . "\n";
    }
    
    // Eklenen ilçeleri doğrula
    echo "\n=== SON KONTROL ===\n";
    $verify_query = "SELECT d.id, d.name, d.city_id, c.name as city_name, c.plate_code 
                     FROM districts d 
                     JOIN cities c ON d.city_id = c.id 
                     WHERE d.city_id = :city_id 
                     ORDER BY d.name";
    $stmt = $db->prepare($verify_query);
    $stmt->bindParam(':city_id', $ankara_id, PDO::PARAM_INT);
    $stmt->execute();
    $final_districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Final kontrol - Ankara (ID: $ankara_id) ilçeleri:\n";
    foreach ($final_districts as $district) {
        $marker = in_array($district['name'], $ankara_districts) ? "✅" : "⚠️";
        echo "$marker {$district['name']} (District ID: {$district['id']}, City ID: {$district['city_id']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Hata oluştu: " . $e->getMessage() . "\n";
    echo "Hata detayı: " . $e->getTraceAsString() . "\n";
}

echo "\n=== İŞLEM TAMAMLANDI ===\n";
?>