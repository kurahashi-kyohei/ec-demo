<?php

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            // Heroku環境の場合はJawsDB URLを使用
            if (getenv("JAWSDB_URL")) {
                $url = parse_url(getenv("JAWSDB_URL"));
                $host = $url["host"];
                $dbname = ltrim($url["path"], '/');
                $username = $url["user"];
                $password = $url["pass"];
            } 
            // ローカル環境の場合は.envの設定を使用
            else {
                $host = getenv("DB_HOST") ?: "127.0.0.1";
                $dbname = getenv("DB_DATABASE") ?: "ec_demo";
                $username = getenv("DB_USERNAME") ?: "root";
                $password = getenv("DB_PASSWORD") ?: "";
            }
            
            $this->connection = new PDO(
                "mysql:host=" . $host . 
                ";dbname=" . $dbname,
                $username,
                $password,
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
        if (getenv("JAWSDB_URL")) {
            $url = parse_url(getenv("JAWSDB_URL"));
            return [
                'host' => $url["host"],
                'database' => ltrim($url["path"], '/'),
                'username' => $url["user"],
                'password' => $url["pass"],
            ];
        }
        
        return [
            'host' => getenv("DB_HOST") ?: "127.0.0.1",
            'database' => getenv("DB_DATABASE") ?: "ec_demo",
            'username' => getenv("DB_USERNAME") ?: "root",
            'password' => getenv("DB_PASSWORD") ?: "",
        ];
    }
} 