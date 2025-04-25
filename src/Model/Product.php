<?php

namespace App\Model;

use App\Config\Database;
use PDO;
use PDOException;
use Exception;

class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getTotalCount() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM products");
        return $stmt->fetchColumn();
    }

    public function getLowStockProducts($limit = 5) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM products
            WHERE stock <= 5
            ORDER BY stock ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $sql = "
            SELECT 
                p.*,
                c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByName($name) {
        $stmt = $this->db->prepare('
            SELECT *
            FROM products
            WHERE name = :name
        ');
        $stmt->execute([':name' => $name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProductsByCategory($category) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE category = ? ORDER BY created_at DESC");
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }

    public function getProductById($id) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getCategories() {
        $stmt = $this->db->query("select * from categories");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchProducts($currentPage = 1, $keyword = null, $category = null, $sort = 'id', $order = 'asc') {
        $limit = 30;
        $offset = ($currentPage - 1) * $limit;

        $sql = "
            SELECT 
                p.*,
                c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($keyword)) {
            $sql .= " AND p.name LIKE :keyword";
            $params[':keyword'] = "%{$keyword}%";
        }
        
        if (!empty($category)) {
            $sql .= " AND p.category_id = :category";
            $params[':category'] = $category;
        }

        // 許可されたソートカラムのリスト
        $allowedColumns = ['id', 'name', 'price', 'stock', 'created_at'];
        
        // ソートカラムのバリデーション
        if (!in_array($sort, $allowedColumns)) {
            $sort = 'id';
        }
        
        // ソート順のバリデーション
        $order = strtolower($order);
        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'asc';
        }

        $sql .= " ORDER BY p.{$sort} {$order} LIMIT {$limit} OFFSET {$offset}";

        try {
            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error in searchProducts: ' . $e->getMessage());
            return [];
        }
    }

    public function getCategoryName($category_id) {
        try {
            $sql = "SELECT name FROM categories WHERE id = :category_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':category_id' => $category_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['name'] : '未分類';
        } catch (PDOException $e) {
            error_log('Error in getCategoryName: ' . $e->getMessage());
            return '未分類';
        }
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO products (name, description, price, stock, image_path, category_id, created_at, updated_at) 
                    VALUES (:name, :description, :price, :stock, :image_path, :category_id, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            
            $params = [
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':price' => $data['price'],
                ':stock' => $data['stock'],
                ':image_path' => $data['image_path'],
                ':category_id' => $data['category_id']
            ];
            
            error_log('Executing SQL: ' . $sql);
            error_log('Parameters: ' . print_r($params, true));
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                error_log('SQL Error: ' . print_r($stmt->errorInfo(), true));
            }
            
            return $result;
        } catch (\PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data) {
        $sql = "UPDATE products 
                SET name = :name, 
                    description = :description, 
                    price = :price, 
                    stock = :stock, 
                    image_path = :image_path,
                    category_id = :category_id,
                    updated_at = NOW() 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':stock' => $data['stock'],
            ':image_path' => $data['image_path'],
            ':category_id' => $data['category_id']
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * 商品の総数を取得
     */
    public function getTotalProducts()
    {
        $sql = "SELECT COUNT(*) as total FROM products";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)$result['total'];
    }

    /**
     * 指定された範囲の商品を取得
     */
    public function getProducts($limit, $offset)
    {
        $sql = "SELECT * FROM products ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllProducts()
    {
        $sql = "
            SELECT 
                p.*,
                c.name as category
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.id DESC
        ";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error in getAllProducts: ' . $e->getMessage());
            return [];
        }
    }

    public function isExist($name) {
        $sql = "SELECT COUNT(*) FROM products WHERE name = :name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':name' => $name]);
        return $stmt->fetchColumn() > 0;
    }
} 
