<?php

require_once __DIR__ . '/../../Config/Database.php';

use App\Config\Database;
use PDOException;

class CreateFavoritesTable
{
    private $db;
    private $tableName = 'favorites';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function up()
    {
        try {
            // トランザクション開始
            $this->db->beginTransaction();

            // テーブルが存在するか確認
            $tableExists = $this->tableExists();
            
            if (!$tableExists) {
                $sql = "CREATE TABLE {$this->tableName} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    product_id INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_favorite (user_id, product_id),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

                $this->db->exec($sql);
                echo "Created favorites table successfully\n";
            } else {
                echo "favorites table already exists\n";
            }

            // トランザクションをコミット
            $this->db->commit();
            echo "Migration completed successfully\n";

        } catch (PDOException $e) {
            // エラーが発生した場合はロールバック
            $this->db->rollBack();
            echo "Migration failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    public function down()
    {
        try {
            // トランザクション開始
            $this->db->beginTransaction();

            // テーブルが存在する場合は削除
            if ($this->tableExists()) {
                $sql = "DROP TABLE {$this->tableName}";
                $this->db->exec($sql);
                echo "Dropped favorites table successfully\n";
            }

            // トランザクションをコミット
            $this->db->commit();
            echo "Rollback completed successfully\n";

        } catch (PDOException $e) {
            // エラーが発生した場合はロールバック
            $this->db->rollBack();
            echo "Rollback failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    private function tableExists()
    {
        try {
            $sql = "SHOW TABLES LIKE '{$this->tableName}'";
            $result = $this->db->query($sql);
            return $result->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}

// マイグレーションの実行
if (php_sapi_name() === 'cli') {
    try {
        $migration = new CreateFavoritesTable();
        
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