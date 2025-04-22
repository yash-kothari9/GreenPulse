<?php
namespace App\Controllers;

use App\Core\Database;

class AuthController {
    private $db;
    private $error;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function logout() {
        session_start();
        session_destroy();
        header('Location: /sdg-market/public/');
        exit;
    }
    
    public function showLogin() {
        $error = $this->error;
        require_once __DIR__ . '/../../views/auth/login.php';
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showLogin();
            return;
        }
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        try {
            $stmt = $this->db->query("SELECT * FROM users WHERE email = ?", [$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                header('Location: /sdg-market/public/');
                exit;
            }
            
            $this->error = 'Invalid email or password';
            $this->showLogin();
        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $this->error = 'An error occurred. Please try again.';
            $this->showLogin();
        }
    }
    
    public function showRegister() {
        $error = $this->error;
        require_once __DIR__ . '/../../views/auth/register.php';
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showRegister();
            return;
        }
        
        $phone = $_POST['phone'] ?? '';
        $registration_number = $_POST['registration_number'] ?? '';
        $college = $_POST['college'] ?? '';
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        
        if (!$name || !$email || !$password || !$confirm || !$college || !$registration_number || !$phone) {
            $this->error = 'All fields are required.';
            $this->showRegister();
            return;
        }
        if ($password !== $confirm) {
            $this->error = 'Passwords do not match.';
            $this->showRegister();
            return;
        }
        
        try {
            // Check if email already exists
            $stmt = $this->db->query("SELECT id FROM users WHERE email = ?", [$email]);
            if ($stmt->fetch()) {
                $this->error = 'Email already registered';
                $this->showRegister();
                return;
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $this->db->query(
                "INSERT INTO users (email, password, name, college, phone, registration_number) VALUES (?, ?, ?, ?, ?, ?)",
                [$email, $hashedPassword, $name, $college, $phone, $registration_number]
            );
            
            // Redirect to login
            header('Location: /sdg-market/public/login');
            exit;
        } catch (\Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $this->error = 'An error occurred. Please try again.';
            $this->showRegister();
        }
    }
}
