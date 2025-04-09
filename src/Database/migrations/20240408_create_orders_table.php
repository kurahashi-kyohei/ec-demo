<?php

require_once __DIR__ . '/../../Config/Database.php';

use App\Config\Database;
use PDOException;

class CreateOrdersTable
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function up()
    {
        try {
            // 外部キー制約のチェックを無効化
            $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');

            // ordersテーブルの作成
            $sql = "CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $this->db->exec($sql);
            echo "Created orders table successfully\n";

            // order_detailsテーブルの作成
            $sql = "CREATE TABLE IF NOT EXISTS order_details (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                quantity INT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $this->db->exec($sql);
            echo "Created order_details table successfully\n";

            // 外部キー制約のチェックを有効化
            $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');

            echo "Migration completed successfully\n";

        } catch (PDOException $e) {
            // エラーが発生した場合は外部キー制約を元に戻す
            $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
            echo "Migration failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    public function down()
    {
        try {
            // 外部キー制約のチェックを無効化
            $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');

            // テーブルの削除
            $this->db->exec('DROP TABLE IF EXISTS order_details');
            echo "Dropped order_details table successfully\n";

            $this->db->exec('DROP TABLE IF EXISTS orders');
            echo "Dropped orders table successfully\n";

            // 外部キー制約のチェックを有効化
            $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');

            echo "Rollback completed successfully\n";

        } catch (PDOException $e) {
            // エラーが発生した場合は外部キー制約を元に戻す
            $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
            echo "Rollback failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}

// マイグレーションの実行
if (php_sapi_name() === 'cli') {
    try {
        $migration = new CreateOrdersTable();
        
        // コマンドライン引数でロールバックかどうかを判断
        $isRollback = isset($argv[1]) && $argv[1] === '--rollback';
        
        if ($isRollback) {
            $migration->down();
        } else {
            $migration->up();
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
} 