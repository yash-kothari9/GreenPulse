<?php
namespace App\Controllers;

use App\Core\Database;

class ListingsController {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function index() {
        try {
            $category = isset($_GET['category']) ? $_GET['category'] : null;
            error_log("Fetching listings for category: " . ($category ?? 'all'));
            
            $query = "SELECT l.*, u.name as user_name, u.college 
                     FROM listings l 
                     JOIN users u ON l.user_id = u.id 
                     WHERE l.status = 'active'";
            
            $params = [];
            if ($category) {
                $query .= " AND l.category = ?";
                $params[] = $category;
            }
            if (isset($_GET['search']) && trim($_GET['search']) !== '') {
                $search = '%' . trim($_GET['search']) . '%';
                $query .= " AND (l.title LIKE ? OR l.description LIKE ?)";
                $params[] = $search;
                $params[] = $search;
            }
            
            $stmt = $this->db->query($query, $params);
            if ($stmt === false) {
                throw new \Exception("Failed to execute query");
            }
            
            $listings = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            error_log("Found " . count($listings) . " listings");
            
            require_once __DIR__ . '/../../views/listings/index.php';
        } catch (\Exception $e) {
            error_log("Error in ListingsController::index: " . $e->getMessage());
            http_response_code(500);
            echo "<h1>Internal Server Error</h1>";
            echo "<p>Sorry, something went wrong. Please try again later.</p>";
            if (getenv('APP_DEBUG')) {
                echo "<pre>Error: " . htmlspecialchars($e->getMessage()) . "</pre>";
            }
        }
    }
    
    public function show($id = null) {
        if ($id === null) {
            // Try to get ID from URL
            $parts = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
            $id = end($parts);
        }
        try {
            $stmt = $this->db->query("SELECT l.*, u.name as user_name, u.college FROM listings l JOIN users u ON l.user_id = u.id WHERE l.id = ?", [$id]);
            $listing = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$listing) {
                http_response_code(404);
                require_once __DIR__ . '/../../views/errors/404.php';
                return;
            }
            require_once __DIR__ . '/../../views/listings/show.php';
        } catch (\Exception $e) {
            error_log("Error in ListingsController::show: " . $e->getMessage());
            http_response_code(500);
            echo "<h1>Internal Server Error</h1>";
        }
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id'])) {
                header('Location: /sdg-market/public/login');
                exit;
            }
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? '';
            $category = $_POST['category'] ?? '';
            $condition = $_POST['condition'] ?? '';
            $registration_number = $_POST['registration_number'] ?? '';
            $user_id = $_SESSION['user_id'];
            if (!$title || !$price || !$category || !$condition || !$description || !$registration_number) {
                $error = 'All fields are required.';
                require __DIR__ . '/../../views/listings/create.php';
                return;
            }
            try {
                $this->db->query(
                    "INSERT INTO listings (title, description, price, category, condition, user_id, registration_number) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [
                        $title,
                        $description,
                        $price,
                        $category,
                        $condition,
                        $user_id,
                        $registration_number
                    ]
                );
                header('Location: /sdg-market/public/listings');
                exit;
            } catch (\PDOException $e) {
                $error = 'Failed to create listing';
                error_log($e->getMessage());
            }
        }
        require_once __DIR__ . '/../../views/listings/create.php';
    }
}
