<?php
/**
 * Bursa'daki tüm ilçeleri ekleyen script
 * Emlak-Delfino Projesi
 * ⚠️ KRITIK: Bursa ID = 4, Plaka = 16
 */

require_once 'config/database.php';

// Veritabanı bağlantısı oluştur
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Veritabanı bağlantısı başarısız!");
}

echo "=== BURSA İLÇELERİNİ KONTROL VE EKLEME ===\n\n";

// Bursa'nın city_id'sini bul ve doğrula
$bursa_query = "SELECT id, name, plate_code FROM cities WHERE name = 'Bursa'";
$stmt = $db->prepare($bursa_query);
$stmt->execute();
$bursa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bursa) {
    die("❌ Bursa ili bulunamadı!");
}

echo "🔍 BURSA BİLGİLERİ:\n";
echo "ID: " . $bursa['id'] . "\n";
echo "İsim: " . $bursa['name'] . "\n";
echo "Plaka: " . $bursa['plate_code'] . "\n\n";

// Kritik kontrol
if ($bursa['plate_code'] !== '16') {
    die("❌ HATA: Bursa'nın plaka kodu 16 olmalı, şu an: " . $bursa['plate_code']);
}

$bursa_id = $bursa['id'];
echo "✅ Bursa ID doğrulandı: $bursa_id\n\n";

// Bursa'daki tüm ilçeler (17 ilçe)
$bursa_districts = [
    'Büyükorhan',
    'Gemlik',
    'Gürsu',
    'Harmancık',
    'İnegöl',
    'İznik',
    'Karacabey',
    'Keles',
    'Kestel',
    'Mudanya',
    'Mustafakemalpaşa',
    'Nilüfer',
    'Orhaneli',
    'Orhangazi',
    'Osmangazi',
    'Yenişehir',
    'Yıldırım'
];

try {
    // Mevcut ilçeleri getir
    $existing_query = "SELECT name FROM districts WHERE city_id = :city_id ORDER BY name";
    $stmt = $db->prepare($existing_query);
    $stmt->bindParam(':city_id', $bursa_id, PDO::PARAM_INT);
    $stmt->execute();
    $existing_districts = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Mevcut ilçe sayısı: " . count($existing_districts) . "\n";
    echo "Toplam olması gereken ilçe sayısı: " . count($bursa_districts) . "\n\n";
    
    if (!empty($existing_districts)) {
        echo "Mevcut ilçeler:\n";
        foreach ($existing_districts as $district) {
            echo "- $district\n";
        }
        echo "\n";
    } else {
        echo "⚠️  Bursa'da henüz hiç ilçe kayıtlı değil.\n\n";
    }
    
    $added_count = 0;
    $skipped_count = 0;
    
    echo "=== BURSA İLÇELERİNİ EKLEME ===\n";
    foreach ($bursa_districts as $district_name) {
        if (in_array($district_name, $existing_districts)) {
            echo "⏭️  $district_name zaten mevcut\n";
            $skipped_count++;
        } else {
            // Yeni ilçe ekle - KRİTİK: city_id = 4 kullan
            $insert_query = "INSERT INTO districts (city_id, name, created_at, updated_at) 
                           VALUES (:city_id, :name, NOW(), NOW())";
            $stmt = $db->prepare($insert_query);
            $stmt->bindParam(':city_id', $bursa_id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $district_name);
            
            if ($stmt->execute()) {
                $new_id = $db->lastInsertId();
                echo "✅ $district_name eklendi (ID: $new_id, city_id: $bursa_id)\n";
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
    $stmt->bindParam(':city_id', $bursa_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Bursa'daki toplam ilçe sayısı: " . $result['total'] . "\n";
    
    if ($result['total'] == 17) {
        echo "🎉 Bursa'daki 17 ilçenin tamamı başarıyla eklendi!\n";
    } else {
        echo "ℹ️  Bursa'daki mevcut ilçe sayısı: " . $result['total'] . "\n";
    }
    
    // Eklenen ilçeleri doğrula
    echo "\n=== SON KONTROL ===\n";
    $verify_query = "SELECT d.id, d.name, d.city_id, c.name as city_name, c.plate_code 
                     FROM districts d 
                     JOIN cities c ON d.city_id = c.id 
                     WHERE d.city_id = :city_id 
                     ORDER BY d.name";
    $stmt = $db->prepare($verify_query);
    $stmt->bindParam(':city_id', $bursa_id, PDO::PARAM_INT);
    $stmt->execute();
    $final_districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Final kontrol - Bursa (ID: $bursa_id) ilçeleri:\n";
    foreach ($final_districts as $district) {
        $marker = in_array($district['name'], $bursa_districts) ? "✅" : "⚠️";
        echo "$marker {$district['name']} (District ID: {$district['id']}, City ID: {$district['city_id']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Hata oluştu: " . $e->getMessage() . "\n";
    echo "Hata detayı: " . $e->getTraceAsString() . "\n";
}

echo "\n=== İŞLEM TAMAMLANDI ===\n";
?>