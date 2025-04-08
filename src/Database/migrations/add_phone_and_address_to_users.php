<?php

require_once __DIR__ . '/../../Config/Database.php';

use App\Config\Database;
use PDOException;

class AddPhoneAndAddressToUsers
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function up()
    {
        $sql = "ALTER TABLE users 
                ADD COLUMN phone_number VARCHAR(20) NOT NULL AFTER email";
        
        try {
            $this->db->exec($sql);
            echo "Migration successful: Added phone_number column to users table\n";
        } catch (PDOException $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
        }
    }

    public function down()
    {
        $sql = "ALTER TABLE users 
                DROP COLUMN phone_number";
        
        try {
            $this->db->exec($sql);
            echo "Rollback successful: Removed phone_number column from users table\n";
        } catch (PDOException $e) {
            echo "Rollback failed: " . $e->getMessage() . "\n";
        }
    }
}

// マイグレーションを実行
$migration = new AddPhoneAndAddressToUsers();
$migration->up(); 