<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Middleware\SessionMiddleware;

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