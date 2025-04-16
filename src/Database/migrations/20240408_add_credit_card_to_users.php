<?php

require_once __DIR__ . '/../../Config/Database.php';

use App\Config\Database;

class AddCreditCardToUsers
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function up()
    {
        try {
            // クレジットカード情報用のカラムを追加
            $sql = "ALTER TABLE users 
                    ADD COLUMN card_cvv VARCHAR(4) NULL,
                    ADD COLUMN card_number VARCHAR(16) NULL,
                    ADD COLUMN card_holder VARCHAR(255) NULL,
                    ADD COLUMN card_expiry VARCHAR(5) NULL,
                    ADD COLUMN card_brand VARCHAR(255) NULL";
            
            $this->db->exec($sql);
            echo "Added credit card columns successfully\n";

        } catch (PDOException $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    public function down()
    {
        try {
            // 追加したカラムを削除
            $sql = "ALTER TABLE users 
                    DROP COLUMN card_number,
                    DROP COLUMN card_holder,
                    DROP COLUMN card_expiry,
                    DROP COLUMN card_brand";
            
            $this->db->exec($sql);
            echo "Dropped credit card columns successfully\n";

        } catch (PDOException $e) {
            echo "Rollback failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}

// マイグレーションの実行
if (php_sapi_name() === 'cli') {
    try {
        $migration = new AddCreditCardToUsers();
        
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