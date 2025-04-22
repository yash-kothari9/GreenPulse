<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "carpool_portal";

// Create connection to MySQL server (without selecting a database yet)
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    phone VARCHAR(15) NOT NULL
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'users' created successfully.<br>";
} else {
    echo "Error creating 'users' table: " . $conn->error . "<br>";
}

// Create locations table
$sql = "CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    latitude DOUBLE NOT NULL,
    longitude DOUBLE NOT NULL
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'locations' created successfully.<br>";
} else {
    echo "Error creating 'locations' table: " . $conn->error . "<br>";
}

// Create rides table
$sql = "CREATE TABLE IF NOT EXISTS rides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    vehicle_type ENUM('2_wheeler', '4_wheeler') NOT NULL,
    vehicle_model VARCHAR(100) NOT NULL,
    vehicle_number VARCHAR(20) NOT NULL,
    capacity INT NOT NULL,
    seats_available INT NOT NULL,
    start_location_id INT NOT NULL,
    end_location_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    carbon_saved FLOAT DEFAULT 0,
    FOREIGN KEY (driver_id) REFERENCES users(id),
    FOREIGN KEY (start_location_id) REFERENCES locations(id),
    FOREIGN KEY (end_location_id) REFERENCES locations(id)
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'rides' created successfully.<br>";
} else {
    echo "Error creating 'rides' table: " . $conn->error . "<br>";
}

// Create ride_passengers table
$sql = "CREATE TABLE IF NOT EXISTS ride_passengers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ride_id INT NOT NULL,
    passenger_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (ride_id) REFERENCES rides(id),
    FOREIGN KEY (passenger_id) REFERENCES users(id)
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'ride_passengers' created successfully.<br>";
} else {
    echo "Error creating 'ride_passengers' table: " . $conn->error . "<br>";
}

// Create emergency_contacts table
$sql = "CREATE TABLE IF NOT EXISTS emergency_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    contact_name VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(15) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'emergency_contacts' created successfully.<br>";
} else {
    echo "Error creating 'emergency_contacts' table: " . $conn->error . "<br>";
}

echo "<br>All tables created successfully!<br>";

$conn->close();
?>