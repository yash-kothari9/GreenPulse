<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = new Database();

// Create a test user
try {
    $db->query(
        "INSERT INTO users (email, password, name, user_type) VALUES (?, ?, ?, ?)",
        ['test@example.com', password_hash('password123', PASSWORD_DEFAULT), 'Test User', 'seller']
    );
    echo "Test user created successfully!\n";
} catch (PDOException $e) {
    echo "Error creating test user: " . $e->getMessage() . "\n";
}

// Create a test product
try {
    $db->query(
        "INSERT INTO products (title, description, price, sdg_category, seller_id) VALUES (?, ?, ?, ?, ?)",
        ['Eco-friendly Water Bottle', 'Reusable water bottle made from recycled materials', 19.99, 12, 1]
    );
    echo "Test product created successfully!\n";
} catch (PDOException $e) {
    echo "Error creating test product: " . $e->getMessage() . "\n";
}

// Verify the data
$users = $db->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
echo "\nUsers in database:\n";
print_r($users);

$products = $db->query("SELECT * FROM products")->fetchAll(PDO::FETCH_ASSOC);
echo "\nProducts in database:\n";
print_r($products);
