<?php

namespace App\Controller\Admin;

use App\Model\Order;
use App\Model\Product;
use App\Model\User;

class DashboardController {
    private $orderModel;
    private $productModel;
    private $userModel;

    public function __construct() {
        $this->orderModel = new Order();
        $this->productModel = new Product();
        $this->userModel = new User();
    }

    public function index() {
        $data = [
            'totalProducts' => $this->productModel->getTotalCount(),
            'totalOrders' => $this->orderModel->getTotalCount(),
            'totalUsers' => $this->userModel->getTotalCount()
        ];

        extract($data);
        require_once __DIR__ . '/../../View/admin/dashboard.php';
    }

    public function getStats() {
        try {
            header('Content-Type: application/json');

            $year = $_GET['year'] ?? date('Y');
        

            $data = [
                'sales' => $this->orderModel->getMonthlySales($year),
                'products' => $this->orderModel->getProductSales(),
                'categories' => $this->orderModel->getCategorySales(),
                'users' => $this->orderModel->getUserSales(),
            ];

            echo json_encode($data);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}