<?php
namespace App\Core;

class Database {
    private $connection;
    
    public function __construct() {
        try {
            $dbPath = __DIR__ . '/../../database/sdg_marketplace.sqlite';
            error_log("Connecting to database at: $dbPath");
            
            if (!file_exists($dbPath)) {
                error_log("Database file does not exist. Creating directory...");
                $dbDir = dirname($dbPath);
                if (!file_exists($dbDir)) {
                    mkdir($dbDir, 0777, true);
                }
                touch($dbPath);
                chmod($dbPath, 0777);
            }
            
            $this->connection = new \PDO("sqlite:$dbPath");
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            error_log("Database connection established successfully");
            
            $this->initializeDatabase();
        } catch(\PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function getLastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    private function initializeDatabase() {
        error_log("Initializing database tables...");
        // Create tables if they don't exist
        $this->connection->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            college VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $this->connection->exec("CREATE TABLE IF NOT EXISTS listings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            category VARCHAR(50) NOT NULL,
            condition VARCHAR(50) NOT NULL,
            user_id INTEGER,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");

        $this->connection->exec("CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            buyer_id INTEGER NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status TEXT CHECK(status IN ('pending', 'completed', 'cancelled')) DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (buyer_id) REFERENCES users(id)
        );");

        $this->connection->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL,
            price_per_unit DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id),
            FOREIGN KEY (product_id) REFERENCES products(id)
        );");
    }
    
    public function query($sql, $params = []) {
        try {
            error_log("Executing SQL: $sql");
            if (!empty($params)) {
                error_log("With params: " . print_r($params, true));
            }
            
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result === false) {
                $error = $stmt->errorInfo();
                error_log("SQL Error: " . print_r($error, true));
                throw new \PDOException("Query execution failed: " . $error[2]);
            }
            
            return $stmt;
        } catch(\PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw $e;
        }
    }
}
