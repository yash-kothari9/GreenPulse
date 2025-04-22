<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty
$dbname = "meal_optout";

// Create connection
$conn = new mysqli($servername, $username, $password);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select database
$conn->select_db($dbname);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    roll_number VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'users' created.<br>";
} else {
    die("Error creating users table: " . $conn->error);
}

// Create meals table
$sql = "CREATE TABLE IF NOT EXISTS meals (
    meal_id INT PRIMARY KEY AUTO_INCREMENT,
    meal_type ENUM('breakfast', 'lunch', 'dinner') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    UNIQUE KEY unique_meal_type (meal_type)
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'meals' created.<br>";
} else {
    die("Error creating meals table: " . $conn->error);
}

// Create meal_optouts table
$sql = "CREATE TABLE IF NOT EXISTS meal_optouts (
    optout_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    meal_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (meal_id) REFERENCES meals(meal_id),
    UNIQUE KEY unique_optout (user_id, meal_id, date)
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'meal_optouts' created.<br>";
} else {
    die("Error creating meal_optouts table: " . $conn->error);
}

// Create refunds table
$sql = "CREATE TABLE IF NOT EXISTS refunds (
    refund_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    month TINYINT NOT NULL,
    year YEAR NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    UNIQUE KEY unique_refund (user_id, month, year)
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'refunds' created.<br>";
} else {
    die("Error creating refunds table: " . $conn->error);
}

// Insert default meal timings
$sql = "INSERT IGNORE INTO meals (meal_type, start_time, end_time, base_price) VALUES
('breakfast', '07:30:00', '09:30:00', 50.00),
('lunch', '12:30:00', '14:30:00', 80.00),
('dinner', '19:30:00', '21:30:00', 80.00)";
if ($conn->query($sql) === TRUE) {
    echo "Default meals inserted.<br>";
} else {
    die("Error inserting meals: " . $conn->error);
}

// Insert admin user (password: admin123, hashed)
$admin_password = password_hash('admin123', PASSWORD_BCRYPT);
$sql = "INSERT IGNORE INTO users (roll_number, full_name, email, password, role) VALUES
('ADMIN001', 'System Admin', 'admin@university.edu', '$admin_password', 'admin')";
if ($conn->query($sql) === TRUE) {
    echo "Admin user inserted.<br>";
} else {
    die("Error inserting admin user: " . $conn->error);
}

echo "<br>Database setup complete!";
$conn->close();
?>