<?php
/**
 * İstanbul'daki eksik ilçeleri ekleyen script
 * Emlak-Delfino Projesi
 */

require_once 'config/database.php';

// Veritabanı bağlantısı oluştur
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Veritabanı bağlantısı başarısız!");
}

echo "=== İSTANBUL İLÇELERİNİ KONTROL VE EKLEME ===\n\n";

// İstanbul'un city_id'sini bul
$istanbul_query = "SELECT id FROM cities WHERE name = 'İstanbul'";
$stmt = $db->prepare($istanbul_query);
$stmt->execute();
$istanbul = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$istanbul) {
    die("İstanbul ili bulunamadı!");
}

$istanbul_id = $istanbul['id'];
echo "İstanbul ID: $istanbul_id\n\n";

// İstanbul'daki tüm ilçeler (39 ilçe)
$istanbul_districts = [
    'Adalar', 'Arnavutköy', 'Ataşehir', 'Avcılar', 'Bağcılar', 'Bahçelievler',
    'Bakırköy', 'Başakşehir', 'Bayrampaşa', 'Beşiktaş', 'Beykoz', 'Beylikdüzü',
    'Beyoğlu', 'Büyükçekmece', 'Çatalca', 'Çekmeköy', 'Esenler', 'Esenyurt',
    'Eyüpsultan', 'Fatih', 'Gaziosmanpaşa', 'Güngören', 'Kadıköy', 'Kağıthane',
    'Kartal', 'Küçükçekmece', 'Maltepe', 'Pendik', 'Sancaktepe', 'Sarıyer',
    'Silivri', 'Sultanbeyli', 'Sultangazi', 'Şile', 'Şişli', 'Tuzla',
    'Ümraniye', 'Üsküdar', 'Zeytinburnu'
];

try {
    // Mevcut ilçeleri getir
    $existing_query = "SELECT name FROM districts WHERE city_id = :city_id";
    $stmt = $db->prepare($existing_query);
    $stmt->bindParam(':city_id', $istanbul_id, PDO::PARAM_INT);
    $stmt->execute();
    $existing_districts = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Mevcut ilçe sayısı: " . count($existing_districts) . "\n";
    echo "Toplam olması gereken ilçe sayısı: " . count($istanbul_districts) . "\n\n";
    
    echo "Mevcut ilçeler:\n";
    foreach ($existing_districts as $district) {
        echo "- $district\n";
    }
    echo "\n";
    
    $added_count = 0;
    $skipped_count = 0;
    
    echo "=== EKSİK İLÇELERİ EKLEME ===\n";
    foreach ($istanbul_districts as $district_name) {
        if (in_array($district_name, $existing_districts)) {
            echo "⏭️  $district_name zaten mevcut\n";
            $skipped_count++;
        } else {
            // Yeni ilçe ekle
            $insert_query = "INSERT INTO districts (city_id, name, created_at, updated_at) 
                           VALUES (:city_id, :name, NOW(), NOW())";
            $stmt = $db->prepare($insert_query);
            $stmt->bindParam(':city_id', $istanbul_id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $district_name);
            
            if ($stmt->execute()) {
                echo "✅ $district_name eklendi\n";
                $added_count++;
            } else {
                echo "❌ $district_name eklenemedi\n";
            }
        }
    }
    
    echo "\n=== ÖZET ===\n";
    echo "Yeni eklenen ilçe sayısı: $added_count\n";
    echo "Zaten mevcut ilçe sayısı: $skipped_count\n";
    
    // Final kontrol
    $final_query = "SELECT COUNT(*) as total FROM districts WHERE city_id = :city_id";
    $stmt = $db->prepare($final_query);
    $stmt->bindParam(':city_id', $istanbul_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "İstanbul'daki toplam ilçe sayısı: " . $result['total'] . "\n";
    
    if ($result['total'] == 39) {
        echo "🎉 İstanbul'daki 39 ilçenin tamamı başarıyla eklendi!\n";
    } else {
        echo "⚠️  İstanbul'daki ilçe sayısı 39 değil. Kontrol edilmesi gerekiyor.\n";
    }
    
    // Eksik olan ilçeleri göster
    $missing_districts = array_diff($istanbul_districts, $existing_districts);
    if (!empty($missing_districts) && $added_count == 0) {
        echo "\nEksik ilçeler:\n";
        foreach ($missing_districts as $missing) {
            echo "- $missing\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Hata oluştu: " . $e->getMessage() . "\n";
}

echo "\n=== İŞLEM TAMAMLANDI ===\n";
?>