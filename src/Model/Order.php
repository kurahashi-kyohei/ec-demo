<?php

namespace App\Model;

use App\Config\Database;
use PDO;
use PDOException;

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
                p.image_path,
                c.name as category_name
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            JOIN categories c ON p.category_id = c.id
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
        try {
            $this->db->beginTransaction();

            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                throw new \Exception("User not logged in");
            }

            $totalAmount = $this->getTotalAmount($cart);
            $productModel = new Product();

            // 注文を作成
            $stmt = $this->db->prepare("
                INSERT INTO orders (user_id, total_amount, order_date, status, created_at, updated_at)
                VALUES (:user_id, :total_amount, CURRENT_TIMESTAMP, 'pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ");

            $stmt->execute([
                ':user_id' => $userId,
                ':total_amount' => $totalAmount
            ]);

            $orderId = $this->db->lastInsertId();

            // 注文詳細を作成
            foreach ($cart as $productId => $quantity) {
                $product = $productModel->getProductById($productId);
                if (!$product) {
                    throw new \Exception("Product not found: " . $productId);
                }

                $stmt = $this->db->prepare("
                    INSERT INTO order_details (order_id, product_id, quantity, price)
                    VALUES (:order_id, :product_id, :quantity, :price)
                ");

                $stmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $productId,
                    ':quantity' => $quantity,
                    ':price' => $product['price']
                ]);
            }

            $this->db->commit();
            return $orderId;

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
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

    public function getMonthlySales($year = null) {
        $year = $year ?? date('Y');
        $sql = "
            SELECT 
                DATE_FORMAT(order_date, '%Y-%m') as date,
                COUNT(*) as order_count,
                SUM(total_amount) as total_amount,
                COUNT(DISTINCT user_id) as unique_customers
            FROM orders 
            WHERE YEAR(order_date) = :year
            GROUP BY DATE_FORMAT(order_date, '%Y-%m')
            ORDER BY date ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':year' => $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductSales($year = null) {
        $year = $year ?? date('Y');
        $sql = "
            SELECT 
                p.id,
                p.name,
                SUM(od.quantity) as total_quantity,
                SUM(od.quantity * od.price) as total_amount
            FROM order_details od
            JOIN orders o ON od.order_id = o.id
            JOIN products p ON od.product_id = p.id
            WHERE YEAR(o.order_date) = :year
            GROUP BY p.id, p.name
            ORDER BY total_amount DESC
            LIMIT 10
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':year' => $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategorySales($year = null) {
        $year = $year ?? date('Y');
        $sql = "
            SELECT 
                c.name as category_name,
                COUNT(DISTINCT o.id) as order_count,
                SUM(od.quantity * od.price) as total_amount
            FROM order_details od
            JOIN orders o ON od.order_id = o.id
            JOIN products p ON od.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            WHERE YEAR(o.order_date) = :year
            GROUP BY c.id, c.name
            ORDER BY total_amount DESC
            LIMIT 10
        ";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':year' => $year]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error in getCategorySales: ' . $e->getMessage());
            return [];
        }
    }

    public function getUserSales($year = null) {
        $year = $year ?? date('Y');
        $sql = "
            SELECT 
                u.id,
                u.first_name,
                u.last_name,
                u.email,
                SUM(o.total_amount) as total_amount
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE YEAR(o.order_date) = :year
            GROUP BY u.id, u.email
            ORDER BY total_amount DESC
            LIMIT 10
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':year' => $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 
