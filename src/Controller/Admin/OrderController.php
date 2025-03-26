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
        $order = $this->orderModel->getOrderWithDetails($id);
        
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

    public function updateStatus($id)
    {
        if (!isset($_POST['status']) || !in_array($_POST['status'], ['pending', 'processing', 'completed', 'cancelled'])) {
            $_SESSION['error'] = '無効なステータスです。';
            header('Location: /admin/orders/' . $id);
            exit();
        }

        $result = $this->orderModel->updateStatus($id, $_POST['status']);

        if ($result) {
            $_SESSION['success'] = '注文ステータスを更新しました。';
        } else {
            $_SESSION['error'] = '注文ステータスの更新に失敗しました。';
        }

        header('Location: /admin/orders/' . $id);
        exit();
    }
} 