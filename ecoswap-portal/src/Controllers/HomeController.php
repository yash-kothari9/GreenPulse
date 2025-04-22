<?php
namespace App\Controllers;

use App\Core\Database;

class HomeController {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function index() {
        $listings = $this->db->query(
            "SELECT l.*, u.name as user_name 
             FROM listings l 
             JOIN users u ON l.user_id = u.id 
             WHERE l.status = 'active'
             ORDER BY l.created_at DESC 
             LIMIT 6"
        )->fetchAll(\PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/../../views/home/index.php';
    }
}
