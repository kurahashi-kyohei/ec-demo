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

    public function getTotalAmount($cart) {
        $productModel = new Product();
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $productModel->getProductById($productId);
            if ($product) {
                $total += $product['price'] * $quantity;
            }
        }

        return $total;
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
                CONCAT(u.last_name, ' ', u.first_name) as user_name,
                u.email as user_email,
                (SELECT COUNT(*) FROM order_details WHERE order_id = o.id) as item_count,
                (SELECT SUM(quantity * price) FROM order_details WHERE order_id = o.id) as total_amount
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrder($id) {
        $stmt = $this->db->prepare("
            SELECT 
                o.*,
                CONCAT(u.last_name, ' ', u.first_name) as user_name,
                u.email,
                (SELECT SUM(quantity * price) FROM order_details WHERE order_id = o.id) as total_amount
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

        $stmt = $this->db->prepare("
            SELECT 
                od.*,
                p.name,
                p.image_path
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            WHERE od.order_id = :order_id
        ");
        $stmt->bindValue(':order_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    public function createOrder($cart) {
        $userId = $_SESSION['user_id'] ?? null;
        $totalAmount = $this->getTotalAmount($cart);

        $stmt = $this->db->prepare("
            INSERT INTO orders (user_id, total_amount, order_date, created_at)
            VALUES (:user_id, :total_amount, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':total_amount' => $totalAmount,
        ]);

        $orderId = $this->db->lastInsertId();

        foreach ($cart as $productId => $quantity) {
            $stmt = $this->db->prepare("
                INSERT INTO order_details (order_id, product_id, quantity, price)
                VALUES (:order_id, :product_id, :quantity, :price)
            ");

            $stmt->execute([
                ':order_id' => $orderId,
                ':product_id' => $productId,
                ':quantity' => $quantity,
                ':price' => $totalAmount
            ]);
        }

        return $orderId;
    }

    public function delete($id) {
        $stmt = $this->db->prepare("
            DELETE FROM orders WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id
        ]);

        $stmt = $this->db->prepare("
            DELETE FROM order_details WHERE order_id = :order_id
        ");
        return $stmt->execute([
            ':order_id' => $id
        ]);

        return $result;
    }

    public function searchOrders($keyword) {
        $stmt = $this->db->prepare("
            SELECT 
                o.*,
                CONCAT(u.last_name, ' ', u.first_name) as user_name,
                u.email,
                (SELECT COUNT(*) FROM order_details WHERE order_id = o.id) as item_count,
                (SELECT SUM(quantity * price) FROM order_details WHERE order_id = o.id) as total_amount
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE CONCAT(u.last_name, ' ', u.first_name) LIKE :keyword
                OR u.email LIKE :keyword
                OR o.id LIKE :keyword
            ORDER BY o.created_at DESC
        ");
        $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 