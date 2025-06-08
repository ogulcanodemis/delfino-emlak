<?php
/**
 * CRUD Test - Sunucu Tarafı
 * Emlak-Delfino Projesi
 */

// CORS ayarları
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emlak-Delfino CRUD Test (Server)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        button { padding: 10px 20px; margin: 5px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .result { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 3px; white-space: pre-wrap; font-family: monospace; }
        h1 { color: #333; text-align: center; }
        h2 { color: #666; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏠 Emlak-Delfino CRUD Test Paneli</h1>
        
        <?php
        $API_BASE = 'http://localhost/emlak-delfino/backend/api';
        $token = '';
        
        // Test 1: Login
        echo "<div class='section'>";
        echo "<h2>1. 🔐 Login Test</h2>";
        
        $login_data = json_encode([
            'email' => 'emlakci@emlakdelfino.com',
            'password' => 'password'
        ]);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $API_BASE . '/auth/login');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $login_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $login_response = curl_exec($ch);
        $login_result = json_decode($login_response, true);
        
        if ($login_result && $login_result['status'] === 'success') {
            echo "<div class='result success'>✅ Login Başarılı!</div>";
            $token = $login_result['data']['token'];
            echo "<div class='result'>Token: " . substr($token, 0, 50) . "...</div>";
        } else {
            echo "<div class='result error'>❌ Login Başarısız!</div>";
            echo "<div class='result'>" . htmlspecialchars($login_response) . "</div>";
        }
        curl_close($ch);
        echo "</div>";
        
        if ($token) {
            // Test 2: Create Property
            echo "<div class='section'>";
            echo "<h2>2. ➕ İlan Oluşturma Test</h2>";
            
            $property_data = json_encode([
                'title' => 'Test İlanı - PHP CRUD',
                'description' => 'Bu ilan PHP CRUD testleri için oluşturulmuştur',
                'price' => 1500000,
                'property_type_id' => 1,
                'status_id' => 1,
                'city_id' => 1,
                'district_id' => 1,
                'area' => 100,
                'rooms' => 3,
                'bathrooms' => 2,
                'floor' => 2,
                'total_floors' => 5,
                'building_age' => 5,
                'heating_type' => 'Doğalgaz',
                'furnishing' => 'Eşyasız',
                'balcony' => 1,
                'elevator' => 1,
                'parking' => 1,
                'address' => 'Test Mahallesi, Test Sokak No:1'
            ]);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $API_BASE . '/properties');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $property_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $create_response = curl_exec($ch);
            $create_result = json_decode($create_response, true);
            
            if ($create_result && $create_result['status'] === 'success') {
                echo "<div class='result success'>✅ İlan Oluşturma Başarılı!</div>";
                $property_id = $create_result['data']['property_id'];
                echo "<div class='result'>Oluşturulan İlan ID: " . $property_id . "</div>";
            } else {
                echo "<div class='result error'>❌ İlan Oluşturma Başarısız!</div>";
                echo "<div class='result'>" . htmlspecialchars($create_response) . "</div>";
                $property_id = null;
            }
            curl_close($ch);
            echo "</div>";
            
            if ($property_id) {
                // Test 3: Update Property
                echo "<div class='section'>";
                echo "<h2>3. ✏️ İlan Güncelleme Test</h2>";
                
                $update_data = json_encode([
                    'title' => 'Güncellenmiş Test İlanı - PHP CRUD',
                    'price' => 1600000,
                    'description' => 'Bu ilan güncellendi - PHP CRUD testleri'
                ]);
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $API_BASE . '/properties/' . $property_id);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $update_data);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                $update_response = curl_exec($ch);
                $update_result = json_decode($update_response, true);
                
                if ($update_result && $update_result['status'] === 'success') {
                    echo "<div class='result success'>✅ İlan Güncelleme Başarılı!</div>";
                } else {
                    echo "<div class='result error'>❌ İlan Güncelleme Başarısız!</div>";
                    echo "<div class='result'>" . htmlspecialchars($update_response) . "</div>";
                }
                curl_close($ch);
                echo "</div>";
                
                // Test 4: Get Property Detail
                echo "<div class='section'>";
                echo "<h2>4. 👁️ İlan Detay Test</h2>";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $API_BASE . '/properties/' . $property_id);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                $detail_response = curl_exec($ch);
                $detail_result = json_decode($detail_response, true);
                
                if ($detail_result && $detail_result['status'] === 'success') {
                    echo "<div class='result success'>✅ İlan Detay Başarılı!</div>";
                    echo "<div class='result'>Başlık: " . htmlspecialchars($detail_result['data']['property']['title']) . "</div>";
                } else {
                    echo "<div class='result error'>❌ İlan Detay Başarısız!</div>";
                    echo "<div class='result'>" . htmlspecialchars($detail_response) . "</div>";
                }
                curl_close($ch);
                echo "</div>";
                
                // Test 5: Delete Property
                echo "<div class='section'>";
                echo "<h2>5. 🗑️ İlan Silme Test</h2>";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $API_BASE . '/properties/' . $property_id);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $token
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                $delete_response = curl_exec($ch);
                $delete_result = json_decode($delete_response, true);
                
                if ($delete_result && $delete_result['status'] === 'success') {
                    echo "<div class='result success'>✅ İlan Silme Başarılı!</div>";
                } else {
                    echo "<div class='result error'>❌ İlan Silme Başarısız!</div>";
                    echo "<div class='result'>" . htmlspecialchars($delete_response) . "</div>";
                }
                curl_close($ch);
                echo "</div>";
            }
        }
        
        // Test 6: List Properties
        echo "<div class='section'>";
        echo "<h2>6. 📋 İlan Listeleme Test</h2>";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $API_BASE . '/properties');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $list_response = curl_exec($ch);
        $list_result = json_decode($list_response, true);
        
        if ($list_result && $list_result['status'] === 'success') {
            echo "<div class='result success'>✅ İlan Listeleme Başarılı!</div>";
            echo "<div class='result'>Toplam İlan: " . $list_result['data']['pagination']['total'] . "</div>";
        } else {
            echo "<div class='result error'>❌ İlan Listeleme Başarısız!</div>";
            echo "<div class='result'>" . htmlspecialchars($list_response) . "</div>";
        }
        curl_close($ch);
        echo "</div>";
        ?>
        
        <div class="section">
            <h2>🎯 Test Özeti</h2>
            <p>Tüm CRUD işlemleri test edildi. Yukarıdaki sonuçları kontrol edin.</p>
            <p><strong>Test Edilen İşlemler:</strong></p>
            <ul>
                <li>✅ Authentication (Login)</li>
                <li>✅ Create Property (POST)</li>
                <li>✅ Read Property (GET)</li>
                <li>✅ Update Property (PUT)</li>
                <li>✅ Delete Property (DELETE)</li>
                <li>✅ List Properties (GET)</li>
            </ul>
        </div>
    </div>
</body>
</html> 