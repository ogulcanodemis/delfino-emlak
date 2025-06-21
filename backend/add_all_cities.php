<?php
/**
 * Türkiye'deki 81 ili veritabanına ekleyen script
 * Emlak-Delfino Projesi
 */

require_once 'config/database.php';

// Veritabanı bağlantısı oluştur
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Veritabanı bağlantısı başarısız!");
}

echo "=== TÜRKİYE'DEKİ 81 İLİ EKLEME ===\n\n";

// Türkiye'deki 81 il listesi (plaka kodları ile)
$cities = [
    ['name' => 'Adana', 'plate_code' => '01'],
    ['name' => 'Adıyaman', 'plate_code' => '02'],
    ['name' => 'Afyonkarahisar', 'plate_code' => '03'],
    ['name' => 'Ağrı', 'plate_code' => '04'],
    ['name' => 'Amasya', 'plate_code' => '05'],
    ['name' => 'Ankara', 'plate_code' => '06'],
    ['name' => 'Antalya', 'plate_code' => '07'],
    ['name' => 'Artvin', 'plate_code' => '08'],
    ['name' => 'Aydın', 'plate_code' => '09'],
    ['name' => 'Balıkesir', 'plate_code' => '10'],
    ['name' => 'Bilecik', 'plate_code' => '11'],
    ['name' => 'Bingöl', 'plate_code' => '12'],
    ['name' => 'Bitlis', 'plate_code' => '13'],
    ['name' => 'Bolu', 'plate_code' => '14'],
    ['name' => 'Burdur', 'plate_code' => '15'],
    ['name' => 'Bursa', 'plate_code' => '16'],
    ['name' => 'Çanakkale', 'plate_code' => '17'],
    ['name' => 'Çankırı', 'plate_code' => '18'],
    ['name' => 'Çorum', 'plate_code' => '19'],
    ['name' => 'Denizli', 'plate_code' => '20'],
    ['name' => 'Diyarbakır', 'plate_code' => '21'],
    ['name' => 'Edirne', 'plate_code' => '22'],
    ['name' => 'Elazığ', 'plate_code' => '23'],
    ['name' => 'Erzincan', 'plate_code' => '24'],
    ['name' => 'Erzurum', 'plate_code' => '25'],
    ['name' => 'Eskişehir', 'plate_code' => '26'],
    ['name' => 'Gaziantep', 'plate_code' => '27'],
    ['name' => 'Giresun', 'plate_code' => '28'],
    ['name' => 'Gümüşhane', 'plate_code' => '29'],
    ['name' => 'Hakkâri', 'plate_code' => '30'],
    ['name' => 'Hatay', 'plate_code' => '31'],
    ['name' => 'Isparta', 'plate_code' => '32'],
    ['name' => 'Mersin', 'plate_code' => '33'],
    ['name' => 'İstanbul', 'plate_code' => '34'],
    ['name' => 'İzmir', 'plate_code' => '35'],
    ['name' => 'Kars', 'plate_code' => '36'],
    ['name' => 'Kastamonu', 'plate_code' => '37'],
    ['name' => 'Kayseri', 'plate_code' => '38'],
    ['name' => 'Kırklareli', 'plate_code' => '39'],
    ['name' => 'Kırşehir', 'plate_code' => '40'],
    ['name' => 'Kocaeli', 'plate_code' => '41'],
    ['name' => 'Konya', 'plate_code' => '42'],
    ['name' => 'Kütahya', 'plate_code' => '43'],
    ['name' => 'Malatya', 'plate_code' => '44'],
    ['name' => 'Manisa', 'plate_code' => '45'],
    ['name' => 'Kahramanmaraş', 'plate_code' => '46'],
    ['name' => 'Mardin', 'plate_code' => '47'],
    ['name' => 'Muğla', 'plate_code' => '48'],
    ['name' => 'Muş', 'plate_code' => '49'],
    ['name' => 'Nevşehir', 'plate_code' => '50'],
    ['name' => 'Niğde', 'plate_code' => '51'],
    ['name' => 'Ordu', 'plate_code' => '52'],
    ['name' => 'Rize', 'plate_code' => '53'],
    ['name' => 'Sakarya', 'plate_code' => '54'],
    ['name' => 'Samsun', 'plate_code' => '55'],
    ['name' => 'Siirt', 'plate_code' => '56'],
    ['name' => 'Sinop', 'plate_code' => '57'],
    ['name' => 'Sivas', 'plate_code' => '58'],
    ['name' => 'Tekirdağ', 'plate_code' => '59'],
    ['name' => 'Tokat', 'plate_code' => '60'],
    ['name' => 'Trabzon', 'plate_code' => '61'],
    ['name' => 'Tunceli', 'plate_code' => '62'],
    ['name' => 'Şanlıurfa', 'plate_code' => '63'],
    ['name' => 'Uşak', 'plate_code' => '64'],
    ['name' => 'Van', 'plate_code' => '65'],
    ['name' => 'Yozgat', 'plate_code' => '66'],
    ['name' => 'Zonguldak', 'plate_code' => '67'],
    ['name' => 'Aksaray', 'plate_code' => '68'],
    ['name' => 'Bayburt', 'plate_code' => '69'],
    ['name' => 'Karaman', 'plate_code' => '70'],
    ['name' => 'Kırıkkale', 'plate_code' => '71'],
    ['name' => 'Batman', 'plate_code' => '72'],
    ['name' => 'Şırnak', 'plate_code' => '73'],
    ['name' => 'Bartın', 'plate_code' => '74'],
    ['name' => 'Ardahan', 'plate_code' => '75'],
    ['name' => 'Iğdır', 'plate_code' => '76'],
    ['name' => 'Yalova', 'plate_code' => '77'],
    ['name' => 'Karabük', 'plate_code' => '78'],
    ['name' => 'Kilis', 'plate_code' => '79'],
    ['name' => 'Osmaniye', 'plate_code' => '80'],
    ['name' => 'Düzce', 'plate_code' => '81']
];

try {
    // Mevcut illeri kontrol et
    $existing_query = "SELECT name, plate_code FROM cities ORDER BY plate_code";
    $stmt = $db->prepare($existing_query);
    $stmt->execute();
    $existing_cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $existing_names = array_column($existing_cities, 'name');
    $existing_plates = array_column($existing_cities, 'plate_code');
    
    echo "Mevcut il sayısı: " . count($existing_cities) . "\n";
    echo "Eklenecek toplam il sayısı: " . count($cities) . "\n\n";
    
    $added_count = 0;
    $updated_count = 0;
    $skipped_count = 0;
    
    foreach ($cities as $city) {
        // İl zaten var mı kontrol et (isme göre)
        if (in_array($city['name'], $existing_names)) {
            // Plaka kodu güncellenecek mi kontrol et
            $existing_key = array_search($city['name'], $existing_names);
            if ($existing_key !== false && $existing_plates[$existing_key] !== $city['plate_code']) {
                // Plaka kodunu güncelle
                $update_query = "UPDATE cities SET plate_code = :plate_code, updated_at = NOW() WHERE name = :name";
                $stmt = $db->prepare($update_query);
                $stmt->bindParam(':plate_code', $city['plate_code']);
                $stmt->bindParam(':name', $city['name']);
                
                if ($stmt->execute()) {
                    echo "✏️  {$city['name']} - Plaka kodu güncellendi: {$city['plate_code']}\n";
                    $updated_count++;
                } else {
                    echo "❌ {$city['name']} güncellenemedi\n";
                }
            } else {
                echo "⏭️  {$city['name']} zaten mevcut\n";
                $skipped_count++;
            }
        } else {
            // Yeni il ekle
            $insert_query = "INSERT INTO cities (name, plate_code, created_at, updated_at) 
                           VALUES (:name, :plate_code, NOW(), NOW())";
            $stmt = $db->prepare($insert_query);
            $stmt->bindParam(':name', $city['name']);
            $stmt->bindParam(':plate_code', $city['plate_code']);
            
            if ($stmt->execute()) {
                echo "✅ {$city['name']} ({$city['plate_code']}) eklendi\n";
                $added_count++;
            } else {
                echo "❌ {$city['name']} eklenemedi\n";
            }
        }
    }
    
    echo "\n=== ÖZET ===\n";
    echo "Yeni eklenen il sayısı: $added_count\n";
    echo "Güncellenen il sayısı: $updated_count\n";
    echo "Atlanan il sayısı: $skipped_count\n";
    
    // Final kontrol
    $final_query = "SELECT COUNT(*) as total FROM cities";
    $stmt = $db->prepare($final_query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Toplam il sayısı: " . $result['total'] . "\n";
    
    if ($result['total'] == 81) {
        echo "🎉 Türkiye'deki 81 ilin tamamı başarıyla eklendi!\n";
    } else {
        echo "⚠️  Toplam il sayısı 81 değil. Kontrol edilmesi gerekiyor.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Hata oluştu: " . $e->getMessage() . "\n";
}

echo "\n=== İŞLEM TAMAMLANDI ===\n";
?>