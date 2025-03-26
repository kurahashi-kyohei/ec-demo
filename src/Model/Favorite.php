<?php

namespace App\Model;

use PDO;
use App\Config\Database;

class Favorite {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function add($userId, $productId) {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO favorites (user_id, product_id)
                VALUES (:user_id, :product_id)
            ');
            return $stmt->execute([
                ':user_id' => $userId,
                ':product_id' => $productId
            ]);
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                return false;
            }
            throw $e;
        }
    }

    public function remove($userId, $productId) {
        $stmt = $this->db->prepare('
            DELETE FROM favorites
            WHERE user_id = :user_id AND product_id = :product_id
        ');
        return $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId
        ]);
    }

    public function isFavorite($userId, $productId) {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) FROM favorites
            WHERE user_id = :user_id AND product_id = :product_id
        ');
        $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function getUserFavorites($userId) {
        $stmt = $this->db->prepare('
            SELECT p.*, f.created_at as favorited_at
            FROM favorites f
            JOIN products p ON f.product_id = p.id
            WHERE f.user_id = :user_id
            ORDER BY f.created_at DESC
        ');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFavoriteCount($productId) {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) FROM favorites
            WHERE product_id = :product_id
        ');
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetchColumn();
    }
} 