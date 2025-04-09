<?php

require_once __DIR__ . '/../../Config/Database.php';

use App\Config\Database;
use PDOException;

class AddTotalAmountAndStatusToOrders
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function up()
    {
        try {
            // テーブル構造を変更
            $sql = "ALTER TABLE orders 
                    ADD COLUMN IF NOT EXISTS total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'pending',
                    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    MODIFY COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            
            $this->db->exec($sql);
            echo "Added total_amount, status, and updated_at columns to orders table successfully\n";

            // 既存の注文の合計金額を更新
            $sql = "UPDATE orders o 
                   SET total_amount = (
                       SELECT COALESCE(SUM(quantity * price), 0) 
                       FROM order_details 
                       WHERE order_id = o.id
                   )";
            $this->db->exec($sql);
            echo "Updated existing orders total_amount successfully\n";

            echo "Migration completed successfully\n";

        } catch (PDOException $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    public function down()
    {
        try {
            // カラムを削除
            $sql = "ALTER TABLE orders 
                    DROP COLUMN IF EXISTS total_amount,
                    DROP COLUMN IF EXISTS status,
                    DROP COLUMN IF EXISTS updated_at";
            
            $this->db->exec($sql);
            echo "Dropped columns successfully\n";

            echo "Rollback completed successfully\n";

        } catch (PDOException $e) {
            echo "Rollback failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}

// マイグレーションの実行
if (php_sapi_name() === 'cli') {
    try {
        $migration = new AddTotalAmountAndStatusToOrders();
        
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