<?php
/**
 * Live veritabanından il ve ilçe verilerini çeken script
 * Emlak-Delfino Projesi
 */

require_once 'config/database.php';
require_once 'models/City.php';
require_once 'models/District.php';

// Veritabanı bağlantısı oluştur
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Veritabanı bağlantısı başarısız!");
}

// Model sınıflarını başlat
$cityModel = new City($db);
$districtModel = new District($db);

echo "=== LIVE VERİTABANI İL VE İLÇE VERİLERİ ===\n\n";

try {
    // Tüm illeri getir
    $cities = $cityModel->getAll();
    
    if (empty($cities)) {
        echo "❌ Veritabanında il verisi bulunamadı!\n";
        
        // Veritabanı tablolarını kontrol et
        echo "\n=== VERİTABANI TABLOLARI KONTROL ===\n";
        $tables_query = "SHOW TABLES";
        $stmt = $db->prepare($tables_query);
        $stmt->execute();
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "Mevcut tablolar: " . implode(", ", $tables) . "\n\n";
        
        // Cities tablosu var mı kontrol et
        if (in_array('cities', $tables)) {
            echo "✓ 'cities' tablosu mevcut\n";
            $count_query = "SELECT COUNT(*) as count FROM cities";
            $stmt = $db->prepare($count_query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Cities tablosundaki kayıt sayısı: " . $result['count'] . "\n";
        } else {
            echo "❌ 'cities' tablosu bulunamadı!\n";
        }
        
        if (in_array('districts', $tables)) {
            echo "✓ 'districts' tablosu mevcut\n";
            $count_query = "SELECT COUNT(*) as count FROM districts";
            $stmt = $db->prepare($count_query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Districts tablosundaki kayıt sayısı: " . $result['count'] . "\n";
        } else {
            echo "❌ 'districts' tablosu bulunamadı!\n";
        }
        
    } else {
        echo "✅ Toplam " . count($cities) . " il bulundu\n\n";
        
        // İlleri listele
        echo "=== İLLER ===\n";
        foreach ($cities as $city) {
            echo "ID: {$city['id']} - {$city['name']} (Plaka: {$city['plate_code']})\n";
            
            // Bu ilin ilçelerini getir
            $districts = $districtModel->getByCity($city['id']);
            if (!empty($districts)) {
                echo "  └─ İlçeler (" . count($districts) . " adet):\n";
                foreach ($districts as $district) {
                    echo "     • {$district['name']}\n";
                }
            } else {
                echo "  └─ İlçe bulunamadı\n";
            }
            echo "\n";
        }
        
        // Özet bilgiler
        echo "\n=== ÖZET ===\n";
        $total_districts = $districtModel->getAll();
        echo "Toplam İl Sayısı: " . count($cities) . "\n";
        echo "Toplam İlçe Sayısı: " . count($total_districts) . "\n";
        
        // En çok ilçesi olan 5 il
        $cities_with_district_count = [];
        foreach ($cities as $city) {
            $district_count = $districtModel->getCityDistrictCount($city['id']);
            $cities_with_district_count[] = [
                'name' => $city['name'],
                'district_count' => $district_count
            ];
        }
        
        // İlçe sayısına göre sırala
        usort($cities_with_district_count, function($a, $b) {
            return $b['district_count'] - $a['district_count'];
        });
        
        echo "\nEn çok ilçesi olan 5 il:\n";
        for ($i = 0; $i < min(5, count($cities_with_district_count)); $i++) {
            $city = $cities_with_district_count[$i];
            echo ($i + 1) . ". {$city['name']}: {$city['district_count']} ilçe\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Hata oluştu: " . $e->getMessage() . "\n";
    echo "Hata detayı: " . $e->getTraceAsString() . "\n";
}

echo "\n=== İŞLEM TAMAMLANDI ===\n";
?>