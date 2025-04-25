<?php

namespace App\Controller;

use App\Model\Product;
use App\Model\Favorite;
use App\Middleware\SessionMiddleware;

class ProductController {
    private $productModel;
    private $favoriteModel;

    public function __construct() {
        SessionMiddleware::start();
        $this->productModel = new Product();
        $this->favoriteModel = new Favorite();
    }

    public function index() {
        $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 10;
        $totalProducts = $this->productModel->getTotalProducts();
        $totalPages = ceil($totalProducts / $perPage);
        $offset = ($currentPage - 1) * $perPage;

        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : null;
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
        $products = $this->productModel->searchProducts($currentPage, $keyword, $category, $sort);
        $categories = $this->productModel->getCategories();

        $data = [
            'products' => $products,
            'categories' => $categories,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'totalProducts' => $totalProducts
        ];

        extract($data);

        require __DIR__ . '/../View/products/index.php';
    }

    public function show($id) {
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            header('Location: /products');
            return;
        }

        $isFavorite = false;
        $favoriteCount = 0;
        
        if (isset($_SESSION['user_id'])) {
            $isFavorite = $this->favoriteModel->isFavorite($_SESSION['user_id'], $id);
        }
        $favoriteCount = $this->favoriteModel->getFavoriteCount($id);

        require __DIR__ . '/../View/products/show.php';
    }
}