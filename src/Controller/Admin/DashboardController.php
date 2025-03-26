<?php

namespace App\Controller\Admin;

use App\Model\Product;
use App\Model\Order;
use App\Model\User;
use App\Middleware\SessionMiddleware;

class DashboardController {
    private $productModel;
    private $orderModel;
    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit();
        }

        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->userModel = new User();
    }

    public function index() {
        $totalProducts = $this->productModel->getTotalCount();
        $totalOrders = $this->orderModel->getTotalCount();
        $totalUsers = $this->userModel->getTotalCount();

        $data = [
            'title' => 'ダッシュボード',
            'totalProducts' => $totalProducts,
            'totalOrders' => $totalOrders,
            'totalUsers' => $totalUsers,
        ];

        // ビューの読み込み
        extract($data);
        require __DIR__ . '/../../View/admin/dashboard.php';
    }
} 