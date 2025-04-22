<?php
namespace App\Core;

class Router {
    private $routes = [];
    
    public function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }
    
    private function matchPath($routePath, $requestPath) {
        // Remove query string from request path
        $requestPath = strtok($requestPath, '?');
        
        // Normalize paths
        $routePath = trim($routePath, '/');
        $requestPath = trim($requestPath, '/');
        
        // If both are empty (root path)
        if ($routePath === '' && $requestPath === '') {
            return true;
        }
        
        // Split paths into parts
        $routeParts = $routePath ? explode('/', $routePath) : [];
        $requestParts = $requestPath ? explode('/', $requestPath) : [];
        
        // If parts count doesn't match
        if (count($routeParts) !== count($requestParts)) {
            return false;
        }
        
        // Compare each part
        foreach ($routeParts as $i => $routePart) {
            if (strpos($routePart, '{') === 0 && strpos($routePart, '}') === strlen($routePart) - 1) {
                continue; // Skip parameter parts
            }
            if ($routePart !== $requestParts[$i]) {
                return false;
            }
        }
        
        return true;
    }
    
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path from URL
        $basePath = '/sdg-market/public';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        // Ensure path starts with /
        if (empty($path)) {
            $path = '/';
        } elseif ($path[0] !== '/') {
            $path = '/' . $path;
        }
        
        error_log("Processing request: $method $path");
        error_log("Query string: " . $_SERVER['QUERY_STRING']);
        
        foreach ($this->routes as $route) {
            error_log("Checking route: {$route['method']} {$route['path']}");
            
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                error_log("Route matched: {$route['path']}");
                $this->handleRoute($route['handler']);
                return;
            }
        }
        
        // No route found
        error_log("No route found for: $method $path");
        http_response_code(404);
        require_once __DIR__ . '/../../views/errors/404.php';
    }
    
    private function handleRoute($handler) {
        if (is_array($handler)) {
            $controller = $handler[0];
            $method = $handler[1];
            $controller->$method();
        } else {
            list($controller, $method) = explode('@', $handler);
            $controllerClass = "App\\Controllers\\$controller";
            $controllerInstance = new $controllerClass();
            $controllerInstance->$method();
        }
    }
}
