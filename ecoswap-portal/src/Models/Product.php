<?php
namespace App\Models;

use App\Core\Database;

class Product {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getAllProducts() {
        $stmt = $this->db->query("SELECT * FROM products");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getProductById($id) {
        $stmt = $this->db->query("SELECT * FROM products WHERE id = ?", [$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function createProduct($data) {
        $sql = "INSERT INTO products (title, description, price, sdg_category, seller_id) 
                VALUES (?, ?, ?, ?, ?)";
        return $this->db->query($sql, [
            $data['title'],
            $data['description'],
            $data['price'],
            $data['sdg_category'],
            $data['seller_id']
        ]);
    }
}
