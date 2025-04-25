<?php

namespace App\Model;

use App\Config\Database;
use PDO;
use PDOException;

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllUsers() {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO users (email, password, first_name, last_name, phone_number, role, status, created_at, updated_at) 
                VALUES (:email, :password, :first_name, :last_name, :phone_number, :role, :status, NOW(), NOW())";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':email' => $data['email'],
                ':password' => $data['password'],
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':phone_number' => $data['phone_number'],
                ':role' => $data['role'] ?? 'user',
                ':status' => $data['status'] ?? 'active'
            ]);
        } catch (PDOException $e) {
            error_log("Failed to create user: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        // 必須フィールドのベースSQL
        $sql = "UPDATE users SET 
                email = :email, 
                first_name = :first_name, 
                last_name = :last_name, 
                phone_number = :phone_number,
                role = :role,
                status = :status,
                updated_at = NOW()";

        // パラメータの初期化
        $params = [
            ':id' => $id,
            ':email' => $data['email'],
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':phone_number' => $data['phone_number'],
            ':role' => $data['role'],
            ':status' => $data['status']
        ];

        // オプションフィールドの処理
        $optionalFields = [
            'address',
            'card_number',
            'card_holder',
            'card_expiry',
            'card_cvv',
            'card_brand'
        ];

        foreach ($optionalFields as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $sql .= ", {$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        $sql .= " WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Error updating user: ' . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTotalCount() {
        $sql = "SELECT COUNT(*) FROM users";
        return $this->db->query($sql)->fetchColumn();
    }

    public function getRecentUsers($limit = 5) {
        $sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $data) {
        $sql = "UPDATE users SET first_name = ?, last_name = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $id
        ]);
    }

    public function updateEmail($id, $email) {
        $sql = "UPDATE users SET email = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$email, $id]);
    }

    public function verifyPassword($inputPassword, $hashedPassword)
    {
        try {
            return password_verify($inputPassword, $hashedPassword);
        } catch (\Exception $e) {
            error_log('Error verifying password: ' . $e->getMessage());
            return false;
        }
    }

    public function createPasswordReset($email) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email, $token, $expires]);
        
        return $token;
    }

    public function verifyResetToken($token) {
        $sql = "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function updatePassword($email, $newPassword) {
        $sql = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            password_hash($newPassword, PASSWORD_DEFAULT),
            $email
        ]);
    }

    public function getAllWithOrderCount() {
        $sql = "
            SELECT 
                u.*,
                CONCAT(u.first_name, ' ', u.last_name) as name,
                COUNT(o.id) as order_count,
                MAX(o.created_at) as last_order_date
            FROM users u
            LEFT JOIN orders o ON u.id = o.user_id
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserWithOrders($id) {
        $sql = "
            SELECT 
                u.*,
                CONCAT(u.first_name, ' ', u.last_name) as name
            FROM users u
            WHERE u.id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        $sql = "
            SELECT 
                o.*,
                (SELECT SUM(quantity * price) FROM order_details WHERE order_id = o.id) as total_amount,
                (SELECT COUNT(*) FROM order_details WHERE order_id = o.id) as item_count
            FROM orders o
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $user['orders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $user;
    }

    public function getOrdersByUserId($id) {
        $sql = "SELECT * FROM orders WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUser($userId, $data)
    {
        try {
            $updateFields = [];
            $params = [':user_id' => $userId];

            // 更新するフィールドの設定
            foreach ($data as $key => $value) {
                if ($value !== null) {
                    $updateFields[] = "{$key} = :{$key}";
                    $params[":{$key}"] = $value;
                }
            }

            if (empty($updateFields)) {
                return false;
            }

            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :user_id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);

        } catch (PDOException $e) {
            error_log('Error updating user: ' . $e->getMessage());
            return false;
        }
    }

    public function createCard($userId, $data) {
        $sql = "UPDATE users SET card_number = ?, card_holder = ?, card_expiry = ?, card_cvv = ?, card_brand = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$data['card_number'], $data['card_holder'], $data['card_expiry'], $data['card_cvv'], $data['card_brand'], $userId]);
    }
} 
