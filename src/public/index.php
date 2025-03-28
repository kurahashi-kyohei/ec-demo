<?php

require_once __DIR__ . '/../../vendor/autoload.php';

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

// URLルーティングの処理
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// ルーティング設定
switch ($path) {
    case '/login':
        require_once '../views/login.php';
        break;
    case '/products':
        require_once '../views/products.php';
        break;
    default:
        // ホームページまたは404ページ
        require_once '../views/home.php';
        break;
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
} catch(Exception $e) {
    echo "接続エラー: " . $e->getMessage();
} 