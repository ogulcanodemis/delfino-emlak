<?php
require_once 'backend/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "=== USERS TABLOSU YAPISI ===\n";
    $descQuery = "DESCRIBE users";
    $descStmt = $db->prepare($descQuery);
    $descStmt->execute();
    $columns = $descStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo $column['Field'] . " - " . $column['Type'] . " - " . $column['Null'] . " - " . $column['Default'] . "\n";
    }
    
    echo "\n=== USERS TABLOSUNDA KAYITLAR ===\n";
    $usersQuery = "SELECT * FROM users LIMIT 3";
    $usersStmt = $db->prepare($usersQuery);
    $usersStmt->execute();
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "ID: " . $user['id'] . " - Email: " . $user['email'] . "\n";
        print_r($user);
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?> 