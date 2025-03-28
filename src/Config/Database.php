<?php

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            // JawsDB URLから接続情報を取得
            $url = parse_url(getenv("JAWSDB_URL"));
            
            $this->connection = new PDO(
                "mysql:host=" . $url["host"] . 
                ";dbname=" . ltrim($url["path"], '/'),
                $url["user"],
                $url["pass"],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch (PDOException $e) {
            die("データベース接続エラー: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // シングルトンパターンを維持するためのメソッド
    private function __clone() {}
    public function __wakeup() {}

    public function getDatabaseConfig() {
        return [
            'host' => $url["host"],
            'database' => ltrim($url["path"], '/'),
            'username' => $url["user"],
            'password' => $url["pass"],
        ];
    }
} 