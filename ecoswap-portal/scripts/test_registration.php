<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = new Database();

try {
    // First, let's see what users are in the database
    echo "Current users in database:\n";
    $users = $db->query("SELECT email FROM users")->fetchAll(PDO::FETCH_ASSOC);
    print_r($users);

    // Try to register a new user
    $email = "test3@example.com";
    $password = password_hash("password123", PASSWORD_DEFAULT);
    $name = "Test User 3";
    $user_type = "buyer";

    $db->query(
        "INSERT INTO users (email, password, name, user_type) VALUES (?, ?, ?, ?)",
        [$email, $password, $name, $user_type]
    );
    
    echo "\nNew user registered successfully!\n";
    
    // Verify the new user was added
    echo "\nUpdated users in database:\n";
    $users = $db->query("SELECT email FROM users")->fetchAll(PDO::FETCH_ASSOC);
    print_r($users);
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
