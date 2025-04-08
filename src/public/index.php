<?php

require_once __DIR__ . '/../../vendor/autoload.php';

// エラーログの設定
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

use Dotenv\Dotenv;
use App\Middleware\SessionMiddleware;
use App\Config\Database;

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();
}

// Start session
SessionMiddleware::start();

// Load router configuration
$router = require __DIR__ . '/../Config/routes.php';

// Run the router
$router->run();

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
} catch(Exception $e) {
    echo "接続エラー: " . $e->getMessage();
} 