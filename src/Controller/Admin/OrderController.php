<?php

namespace App\Controller\Admin;

use App\Model\Order;

class OrderController
{
    private $orderModel;

    public function __construct()
    {
        // セッションが開始されていない場合は開始
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 管理者権限チェック
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit();
        }

        $this->orderModel = new Order();
    }

    public function index()
    {
        $orders = $this->orderModel->getAllOrders();
        
        $data = [
            'title' => '注文管理',
            'orders' => $orders
        ];

        extract($data);
        require __DIR__ . '/../../View/admin/orders/index.php';
    }

    public function show($id)
    {
        $order = $this->orderModel->getOrder($id);
        
        if (!$order) {
            $_SESSION['error'] = '注文が見つかりませんでした。';
            header('Location: /admin/orders');
            exit();
        }

        $data = [
            'title' => '注文詳細 #' . $id,
            'order' => $order
        ];

        extract($data);
        require __DIR__ . '/../../View/admin/orders/detail.php';
    }

    public function delete() {
        if (!isset($_POST['id'])) {
            $_SESSION['error'] = '注文IDが指定されていません。';
            header('Location: /admin/orders');
            exit();
        }

        $result = $this->orderModel->delete($_POST['id']);

        if ($result) {
            $_SESSION['success'] = '注文を削除しました。';
        } else {
            $_SESSION['error'] = '注文の削除に失敗しました。';
        }

        header('Location: /admin/orders');
        exit();
    }

    public function search() {
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        $orders = $this->orderModel->searchOrders($keyword);

        $data = [
            'title' => '注文検索',
            'orders' => $orders,
            'keyword' => $keyword
        ];

        extract($data);
        require __DIR__ . '/../../View/admin/orders/index.php';
    }
} 