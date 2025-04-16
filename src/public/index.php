<?php

require_once __DIR__ . '/../../vendor/autoload.php';

// エラーログの設定
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

use Dotenv\Dotenv;
use App\Middleware\SessionMiddleware;
use App\Config\Database;

// .envファイルから環境変数を読み込む
if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../', null, false);
    $dotenv->load();

    // 重要な環境変数をputenvでも設定
    foreach (['GOOGLE_CLIENT_ID', 'GOOGLE_CLIENT_SECRET', 'APP_URL'] as $key) {
        if (isset($_ENV[$key])) {
            putenv("$key=" . $_ENV[$key]);
        }
    }
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