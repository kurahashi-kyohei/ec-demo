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

    private function columnExists($tableName, $columnName)
    {
        $sql = "SHOW COLUMNS FROM {$tableName} LIKE :column";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':column' => $columnName]);
        return $stmt->rowCount() > 0;
    }

    public function up()
    {
        try {
            // total_amountカラムの追加
            if (!$this->columnExists('orders', 'total_amount')) {
                $sql = "ALTER TABLE orders 
                        ADD COLUMN total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00";
                $this->db->exec($sql);
                echo "Added total_amount column successfully\n";
            }

            // statusカラムの追加
            if (!$this->columnExists('orders', 'status')) {
                $sql = "ALTER TABLE orders 
                        ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'";
                $this->db->exec($sql);
                echo "Added status column successfully\n";
            }

            // updated_atカラムの追加
            if (!$this->columnExists('orders', 'updated_at')) {
                $sql = "ALTER TABLE orders 
                        ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
                $this->db->exec($sql);
                echo "Added updated_at column successfully\n";
            }

            // created_atカラムの修正
            $sql = "ALTER TABLE orders 
                    MODIFY COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            $this->db->exec($sql);
            echo "Modified created_at column successfully\n";

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
            // カラムの存在確認と削除
            if ($this->columnExists('orders', 'total_amount')) {
                $this->db->exec("ALTER TABLE orders DROP COLUMN total_amount");
                echo "Dropped total_amount column successfully\n";
            }

            if ($this->columnExists('orders', 'status')) {
                $this->db->exec("ALTER TABLE orders DROP COLUMN status");
                echo "Dropped status column successfully\n";
            }

            if ($this->columnExists('orders', 'updated_at')) {
                $this->db->exec("ALTER TABLE orders DROP COLUMN updated_at");
                echo "Dropped updated_at column successfully\n";
            }

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