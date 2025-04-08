<?php

namespace App\Model;

use App\Config\Database;
use PDO;

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
        $stmt = $this->db->prepare('
            SELECT *
            FROM products
            WHERE id = :id
        ');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllProducts() {
        $stmt = $this->db->query("SELECT * FROM products ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $stmt = $this->db->query("SELECT DISTINCT category FROM products ORDER BY category");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function searchProducts($currentPage =1, $keyword = null, $category = null) {
        $limit = 10;
        $offset = ($currentPage - 1) * $limit;
    
        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];
        
        if (!empty($keyword)) {
            $sql .= " AND name LIKE :keyword";
            $params[':keyword'] = "%{$keyword}%";
        }
        
        if (!empty($category)) {
            $sql .= " AND category = :category";
            $params[':category'] = $category;
        }

        $sql .= " ORDER BY id LIMIT :limit OFFSET :offset";
    
        $stmt = $this->db->prepare($sql);
        
        // 検索条件のパラメータをバインド
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        // LIMIT と OFFSET を別途バインド
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $products;
    }
    // public function searchProducts($currentPage =1, $keyword = null, $category = null) {

    //     $offset = ($currentPage - 1) * 10;
    //     $limit = 10;
        
    //     $sql = "SELECT * FROM products WHERE 1=1";
    //     $stmt = $this->db->prepare($sql);

    //     if (!empty($keyword)) {
    //         $sql .= " AND name LIKE :keyword";
    //         $stmt->bindParam('keyword', $keyword, PDO::PARAM_STR);
    //     }

    //     if (!empty($category)) {
    //         $sql .= " AND category = :category";
    //         $stmt->bindParam('category', $category, PDO::PARAM_STR);
    //     }

    //     // $countSql = str_replace("SELECT *", "SELECT COUNT(*) as count", $sql);
    //     // $countStmt = $this->db->prepare($countSql);
    //     // $countStmt->execute($params);
    //     // $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];

    //     $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
    //     $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
    //     $stmt->bindParam('offset', $offset, PDO::PARAM_INT);
        
    //     $stmt->execute();
    //     return $stmt->fetchAll();
    // }

    public function create($data) {
        try {
            $sql = "INSERT INTO products (name, description, price, stock, image_path, category, created_at, updated_at) 
                    VALUES (:name, :description, :price, :stock, :image_path, :category, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            
            $params = [
                ':name' => $data['name'],
                ':description' => $data['description'],
                ':price' => $data['price'],
                ':stock' => $data['stock'],
                ':image_path' => $data['image_path'],
                ':category' => $data['category']
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
                    category = :category,
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
            ':category' => $data['category']
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
} 