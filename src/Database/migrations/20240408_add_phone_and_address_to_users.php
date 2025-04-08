<?php

require_once __DIR__ . '/../../Config/Database.php';

use App\Config\Database;

class AddPhoneAndAddressToUsers
{
    private $db;
    private $tableName = 'users';

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
            $columns = $this->getColumns();
            
            // phone_numberカラムの追加
            if (!in_array('phone_number', $columns)) {
                $sql = "ALTER TABLE {$this->tableName} 
                        ADD COLUMN phone_number VARCHAR(20) DEFAULT NULL AFTER email";
                $this->db->exec($sql);
                echo "Added phone_number column to users table\n";
            } else {
                echo "phone_number column already exists\n";
            }

            // addressカラムの追加
            if (!in_array('address', $columns)) {
                $sql = "ALTER TABLE {$this->tableName} 
                        ADD COLUMN address TEXT DEFAULT NULL AFTER phone_number";
                $this->db->exec($sql);
                echo "Added address column to users table\n";
            } else {
                echo "address column already exists\n";
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

            // カラムが存在するか確認
            $columns = $this->getColumns();
            
            // phone_numberカラムの削除
            if (in_array('phone_number', $columns)) {
                $sql = "ALTER TABLE {$this->tableName} DROP COLUMN phone_number";
                $this->db->exec($sql);
                echo "Removed phone_number column from users table\n";
            }

            // addressカラムの削除
            if (in_array('address', $columns)) {
                $sql = "ALTER TABLE {$this->tableName} DROP COLUMN address";
                $this->db->exec($sql);
                echo "Removed address column from users table\n";
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

    private function getColumns()
    {
        $sql = "SHOW COLUMNS FROM {$this->tableName}";
        $stmt = $this->db->query($sql);
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return array_map('strtolower', $columns);
    }
}

// マイグレーションの実行
if (php_sapi_name() === 'cli') {
    try {
        $migration = new AddPhoneAndAddressToUsers();
        
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