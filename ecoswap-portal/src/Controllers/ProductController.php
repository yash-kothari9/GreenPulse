<?php
namespace App\Controllers;

use App\Models\Product;

class ProductController {
    private $productModel;
    
    public function __construct() {
        $this->productModel = new Product();
    }
    
    public function index() {
        $products = $this->productModel->getAllProducts();
        $this->render('products/index', ['products' => $products]);
    }
    
    public function show($id) {
        $product = $this->productModel->getProductById($id);
        $this->render('products/show', ['product' => $product]);
    }
    
    private function render($view, $data = []) {
        extract($data);
        require_once __DIR__ . "/../../views/$view.php";
    }
}
