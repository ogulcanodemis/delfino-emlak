<?php
/**
 * İzmir'daki tüm ilçeleri ekleyen script
 * Emlak-Delfino Projesi
 * ⚠️ KRITIK: İzmir ID = 3, Plaka = 35
 */

require_once 'config/database.php';

// Veritabanı bağlantısı oluştur
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Veritabanı bağlantısı başarısız!");
}

echo "=== İZMİR İLÇELERİNİ KONTROL VE EKLEME ===\n\n";

// İzmir'in city_id'sini bul ve doğrula
$izmir_query = "SELECT id, name, plate_code FROM cities WHERE name = 'İzmir'";
$stmt = $db->prepare($izmir_query);
$stmt->execute();
$izmir = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$izmir) {
    die("❌ İzmir ili bulunamadı!");
}

echo "🔍 İZMİR BİLGİLERİ:\n";
echo "ID: " . $izmir['id'] . "\n";
echo "İsim: " . $izmir['name'] . "\n";
echo "Plaka: " . $izmir['plate_code'] . "\n\n";

// Kritik kontrol
if ($izmir['plate_code'] !== '35') {
    die("❌ HATA: İzmir'in plaka kodu 35 olmalı, şu an: " . $izmir['plate_code']);
}

$izmir_id = $izmir['id'];
echo "✅ İzmir ID doğrulandı: $izmir_id\n\n";

// İzmir'deki tüm ilçeler (30 ilçe)
$izmir_districts = [
    'Aliağa',
    'Balçova', 
    'Bayındır',
    'Bayraklı',
    'Bergama',
    'Beydağ',
    'Bornova',
    'Buca',
    'Çeşme',
    'Çiğli',
    'Dikili',
    'Foça',
    'Gaziemir',
    'Güzelbahçe',
    'Karabağlar',
    'Karaburun',
    'Karşıyaka',
    'Kemalpaşa',
    'Kınık',
    'Kiraz',
    'Konak',
    'Menderes',
    'Menemen',
    'Narlıdere',
    'Ödemiş',
    'Seferihisar',
    'Selçuk',
    'Tire',
    'Torbalı',
    'Urla'
];

try {
    // Mevcut ilçeleri getir
    $existing_query = "SELECT name FROM districts WHERE city_id = :city_id ORDER BY name";
    $stmt = $db->prepare($existing_query);
    $stmt->bindParam(':city_id', $izmir_id, PDO::PARAM_INT);
    $stmt->execute();
    $existing_districts = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Mevcut ilçe sayısı: " . count($existing_districts) . "\n";
    echo "Toplam olması gereken ilçe sayısı: " . count($izmir_districts) . "\n\n";
    
    if (!empty($existing_districts)) {
        echo "Mevcut ilçeler:\n";
        foreach ($existing_districts as $district) {
            echo "- $district\n";
        }
        echo "\n";
    }
    
    $added_count = 0;
    $skipped_count = 0;
    $updated_count = 0;
    
    echo "=== İZMİR İLÇELERİNİ EKLEME ===\n";
    foreach ($izmir_districts as $district_name) {
        if (in_array($district_name, $existing_districts)) {
            echo "⏭️  $district_name zaten mevcut\n";
            $skipped_count++;
        } else {
            // Yeni ilçe ekle - KRİTİK: city_id = 3 kullan
            $insert_query = "INSERT INTO districts (city_id, name, created_at, updated_at) 
                           VALUES (:city_id, :name, NOW(), NOW())";
            $stmt = $db->prepare($insert_query);
            $stmt->bindParam(':city_id', $izmir_id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $district_name);
            
            if ($stmt->execute()) {
                $new_id = $db->lastInsertId();
                echo "✅ $district_name eklendi (ID: $new_id, city_id: $izmir_id)\n";
                $added_count++;
            } else {
                echo "❌ $district_name eklenemedi\n";
                print_r($stmt->errorInfo());
            }
        }
    }
    
    // Alsancak kontrolü - bu gerçek bir ilçe değil, Konak'ın mahallesi
    if (in_array('Alsancak', $existing_districts)) {
        echo "\n⚠️  DİKKAT: 'Alsancak' gerçek bir ilçe değil, Konak ilçesinin mahallesidir.\n";
        echo "Alsancak kaydını silmek için onay gerekir.\n";
    }
    
    echo "\n=== ÖZET ===\n";
    echo "Yeni eklenen ilçe sayısı: $added_count\n";
    echo "Zaten mevcut ilçe sayısı: $skipped_count\n";
    
    // Final kontrol - KRITIK: Doğru city_id ile kontrol et
    $final_query = "SELECT COUNT(*) as total FROM districts WHERE city_id = :city_id";
    $stmt = $db->prepare($final_query);
    $stmt->bindParam(':city_id', $izmir_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "İzmir'deki toplam ilçe sayısı: " . $result['total'] . "\n";
    
    if ($result['total'] == 30) {
        echo "🎉 İzmir'deki 30 ilçenin tamamı başarıyla eklendi!\n";
    } else {
        echo "ℹ️  İzmir'deki mevcut ilçe sayısı: " . $result['total'] . "\n";
        if ($result['total'] > 30) {
            echo "⚠️  Fazla ilçe var, kontrol edilmesi gerekiyor (Alsancak gibi mahalle kayıtları olabilir).\n";
        }
    }
    
    // Eklenen ilçeleri doğrula
    echo "\n=== SON KONTROL ===\n";
    $verify_query = "SELECT d.id, d.name, d.city_id, c.name as city_name, c.plate_code 
                     FROM districts d 
                     JOIN cities c ON d.city_id = c.id 
                     WHERE d.city_id = :city_id 
                     ORDER BY d.name";
    $stmt = $db->prepare($verify_query);
    $stmt->bindParam(':city_id', $izmir_id, PDO::PARAM_INT);
    $stmt->execute();
    $final_districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Final kontrol - İzmir (ID: $izmir_id) ilçeleri:\n";
    foreach ($final_districts as $district) {
        $marker = in_array($district['name'], $izmir_districts) ? "✅" : "⚠️";
        echo "$marker {$district['name']} (District ID: {$district['id']}, City ID: {$district['city_id']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Hata oluştu: " . $e->getMessage() . "\n";
    echo "Hata detayı: " . $e->getTraceAsString() . "\n";
}

echo "\n=== İŞLEM TAMAMLANDI ===\n";
?>