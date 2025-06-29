<?php
// Test profil resmi sorgusu
require_once 'backend/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Kullanıcıları ve profil resimlerini kontrol et
    $query = "SELECT id, name, email, profile_image FROM users WHERE profile_image IS NOT NULL AND profile_image != ''";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Profil resmi olan kullanıcılar:\n";
    foreach ($users as $user) {
        echo "ID: " . $user['id'] . ", Name: " . $user['name'] . ", Profile Image: " . $user['profile_image'] . "\n";
    }
    
    echo "\n\nTest property query:\n";
    $query = "SELECT p.id, p.title, u.name as user_name, u.profile_image as user_profile_image 
              FROM properties p 
              LEFT JOIN users u ON p.user_id = u.id 
              WHERE p.id = 70";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $property = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Property 70 data:\n";
    print_r($property);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>