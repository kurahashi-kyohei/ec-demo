<?php

namespace App\Model;

use App\Config\Database;
use PDO;

class Order {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getTotalCount() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM orders");
        return $stmt->fetchColumn();
    }

    public function getRecentOrders($limit = 5) {
        $stmt = $this->db->prepare("
            SELECT 
                o.*,
                u.email as user_email,
                (SELECT COUNT(*) FROM order_details WHERE order_id = o.id) as item_count,
                (SELECT SUM(quantity * price) FROM order_details WHERE order_id = o.id) as total_amount
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllOrders() {
        $stmt = $this->db->query("
            SELECT 
                o.*,
                CONCAT(u.first_name, ' ', u.last_name) as user_name,
                u.email as user_email,
                (SELECT COUNT(*) FROM order_details WHERE order_id = o.id) as item_count,
                (SELECT SUM(quantity * price) FROM order_details WHERE order_id = o.id) as total_amount
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderWithDetails($id) {
        // 注文の基本情報を取得
        $stmt = $this->db->prepare("
            SELECT o.*, u.email as user_email, u.first_name, u.last_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        // 注文の詳細情報を取得
        $stmt = $this->db->prepare("
            SELECT od.*, p.name as product_name, p.image_path
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            WHERE od.order_id = :order_id
        ");
        $stmt->bindValue(':order_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $order['details'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 合計金額を計算
        $order['total_amount'] = array_reduce($order['details'], function($carry, $item) {
            return $carry + ($item['quantity'] * $item['price']);
        }, 0);

        return $order;
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("
            UPDATE orders
            SET status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':status' => $status
        ]);
    }
} 