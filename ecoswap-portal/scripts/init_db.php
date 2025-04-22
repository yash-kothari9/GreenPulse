<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    // This will create a new database and initialize the tables
    $db = new Database();
    // Create messages table for chat functionality
    $db->query("CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        listing_id INTEGER NOT NULL,
        sender_id INTEGER NOT NULL,
        receiver_id INTEGER NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (listing_id) REFERENCES listings(id),
        FOREIGN KEY (sender_id) REFERENCES users(id),
        FOREIGN KEY (receiver_id) REFERENCES users(id)
    )");
    // Create notifications table for unread message notifications
    $db->query("CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        message_id INTEGER NOT NULL,
        is_read INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (message_id) REFERENCES messages(id)
    )");
    
    echo "Database initialized successfully!\n";
    
    // Create some sample users
    $users = [
        [
            'john@university.edu',
            password_hash('password123', PASSWORD_DEFAULT),
            'John Smith',
            'State University',
            '555-0101',
            'REG12345'
        ],
        [
            'emma@college.edu',
            password_hash('password123', PASSWORD_DEFAULT),
            'Emma Wilson',
            'City College',
            '555-0102',
            'REG54321'
        ]
    ];
    
    foreach ($users as $user) {
        $db->query(
            "INSERT INTO users (email, password, name, college, phone, registration_number) VALUES (?, ?, ?, ?, ?, ?)",
            $user
        );
    }
    
    // Create sample listings
    $listings = [
        [
            'Calculus Textbook 5th Edition',
            'Like new condition, no highlights or notes',
            45.99,
            'books',
            'excellent',
            1
        ],
        [
            'TI-84 Plus Calculator',
            'Used but in perfect working condition. Includes batteries and case',
            65.00,
            'electronics',
            'good',
            1
        ],
        [
            'Udemy Web Development Course',
            'Complete 2025 Web Developer Bootcamp. 9 months access remaining',
            29.99,
            'courses',
            'new',
            2
        ],
        [
            'MacBook Pro 2023',
            '13-inch, 16GB RAM, 512GB SSD. AppleCare+ until 2024',
            1299.99,
            'electronics',
            'like_new',
            2
        ]
    ];
    
    foreach ($listings as $listing) {
        $db->query(
            "INSERT INTO listings (title, description, price, category, condition, user_id) VALUES (?, ?, ?, ?, ?, ?)",
            $listing
        );
    }
    
    echo "Sample data created successfully!\n";
    
    // Verify the data
    $users = $db->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nUsers in database:\n";
    print_r($users);
    
    $listings = $db->query("SELECT * FROM listings")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nListings in database:\n";
    print_r($listings);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
