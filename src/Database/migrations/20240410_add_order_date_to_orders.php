<?php

require_once __DIR__ . '/../../Config/Database.php';

use App\Config\Database;
use PDOException;

class AddOrderDateToOrders
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
            $this->db->beginTransaction();

            // order_dateカラムの追加
            if (!$this->columnExists('orders', 'order_date')) {
                $sql = "ALTER TABLE orders 
                        ADD COLUMN order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER user_id";
                $this->db->exec($sql);
                echo "Added order_date column successfully\n";

                // 既存の注文のorder_dateをcreated_atの値で更新
                $sql = "UPDATE orders SET order_date = created_at WHERE order_date IS NULL";
                $this->db->exec($sql);
                echo "Updated existing orders order_date successfully\n";
            }

            $this->db->commit();
            echo "Migration completed successfully\n";

        } catch (PDOException $e) {
            $this->db->rollBack();
            echo "Migration failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    public function down()
    {
        try {
            $this->db->beginTransaction();

            // order_dateカラムの削除
            if ($this->columnExists('orders', 'order_date')) {
                $sql = "ALTER TABLE orders DROP COLUMN order_date";
                $this->db->exec($sql);
                echo "Dropped order_date column successfully\n";
            }

            $this->db->commit();
            echo "Rollback completed successfully\n";

        } catch (PDOException $e) {
            $this->db->rollBack();
            echo "Rollback failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}

// マイグレーションの実行
if (php_sapi_name() === 'cli') {
    try {
        $migration = new AddOrderDateToOrders();
        
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