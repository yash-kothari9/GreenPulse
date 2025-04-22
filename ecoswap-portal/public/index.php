<?php
session_start();
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Database;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\ListingsController;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize database connection
$db = new Database();

// Initialize router
$router = new Router();

// Define routes
$router->get('/', [new HomeController(), 'index']);
$router->get('/login', [new AuthController(), 'showLogin']);
$router->post('/auth/login', [new AuthController(), 'login']);
$router->get('/register', [new AuthController(), 'showRegister']);
$router->post('/auth/register', [new AuthController(), 'register']);

// Listings routes
$router->get('/listings', [new ListingsController(), 'index']);
$router->get('/listings/create', [new ListingsController(), 'create']);
$router->post('/listings/create', [new ListingsController(), 'create']);
$router->get('/listings/{id}', [new ListingsController(), 'show']);
$router->get('/listings/{id}/chat', [new \App\Controllers\MessagesController(), 'chat']);
$router->post('/listings/{id}/chat/send', [new \App\Controllers\MessagesController(), 'send']);
$router->get('/messages', [new \App\Controllers\MessagesController(), 'inbox']);

$router->get('/logout', [new \App\Controllers\AuthController(), 'logout']);

// Handle the request
$router->dispatch();
