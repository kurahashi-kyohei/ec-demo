<?php

namespace App\Database;

use App\Config\Database;
use PDO;
use PDOException;

class Migration {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function migrate() {
        try {
            $sql = file_get_contents(__DIR__ . '/migrations/create_tables.sql');
            $statements = array_filter(array_map('trim', explode(';', $sql)));

            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $this->db->exec($statement);
                }
            }
            echo "マイグレーションが正常に完了しました。\n";
        } catch (PDOException $e) {
            die("マイグレーションエラー: " . $e->getMessage() . "\n");
        }
    }
} 