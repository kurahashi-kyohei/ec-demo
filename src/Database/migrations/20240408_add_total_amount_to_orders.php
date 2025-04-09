<?php

require_once __DIR__ . '/../../Config/Database.php';

use App\Config\Database;
use PDOException;

class AddTotalAmountToOrders
{
    private $db;
    private $tableName = 'orders';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function up()
    {
        try {
            // トランザクション開始
            $this->db->beginTransaction();

            // カラムが存在するか確認
            $columnExists = $this->columnExists('total_amount');
            
            if (!$columnExists) {
                $sql = "ALTER TABLE {$this->tableName} 
                        ADD COLUMN total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER user_id";
                $this->db->exec($sql);
                echo "Added total_amount column to orders table successfully\n";

                // 既存の注文の合計金額を更新
                $sql = "UPDATE {$this->tableName} o 
                       SET total_amount = (
                           SELECT COALESCE(SUM(quantity * price), 0) 
                           FROM order_details 
                           WHERE order_id = o.id
                       )";
                $this->db->exec($sql);
                echo "Updated existing orders total_amount successfully\n";
            } else {
                echo "total_amount column already exists\n";
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

            // カラムが存在する場合は削除
            if ($this->columnExists('total_amount')) {
                $sql = "ALTER TABLE {$this->tableName} DROP COLUMN total_amount";
                $this->db->exec($sql);
                echo "Dropped total_amount column successfully\n";
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

    private function columnExists($columnName)
    {
        try {
            $sql = "SHOW COLUMNS FROM {$this->tableName} LIKE :column";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':column' => $columnName]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}

// マイグレーションの実行
if (php_sapi_name() === 'cli') {
    try {
        $migration = new AddTotalAmountToOrders();
        
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