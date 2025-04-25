<?php

use Bramus\Router\Router;
use App\Controller\ProductController;
use App\Controller\AuthController;
use App\Controller\MyPageController;
use App\Controller\CartController;
use App\Controller\FavoriteController;
use App\Controller\Admin\ProductController as AdminProductController;
use App\Controller\Admin\OrderController as AdminOrderController;
use App\Controller\Admin\UserController as AdminUserController;
use App\Controller\Admin\DashboardController as AdminDashboardController;
use App\Controller\SocialAuthController;
$router = new Router();

// ホームページ
$router->get('/', function() {
    require __DIR__ . '/../View/home.php';
});

// 商品関連
$router->get('/products', function() {
    $controller = new ProductController();
    $controller->index();
});

$router->get('/products/(\d+)', function($id) {
    $controller = new ProductController();
    $controller->show($id);
});

// 認証関連
$router->get('/login', function() {
    $controller = new AuthController();
    $controller->showLoginForm();
});

$router->post('/login', function() {
    $controller = new AuthController();
    $controller->login();
});

$router->get('/register', function() {
    $controller = new AuthController();
    $controller->showRegisterForm();
});

$router->post('/register', function() {
    $controller = new AuthController();
    $controller->register();
});

$router->post('/logout', function() {
    $controller = new AuthController();
    $controller->logout();
});

$router->get('/auth/google', function() {
    $controller = new AuthController();
    $controller->redirectToGoogle();
});

$router->get('/auth/google/callback', function() {
    $controller = new AuthController();
    $controller->handleGoogleCallback();
});
// マイページ関連
$router->get('/mypage', function() {
    $controller = new MyPageController;
    $controller->index();
});

$router->get('/mypage/edit', function() {
    $controller = new MyPageController;
    $controller->edit();
});

$router->get('/mypage/card/register', function() {
    $controller = new MyPageController;
    $controller->cardRegister();
});

$router->post('/mypage/card/store', function() {
    $controller = new MyPageController;
    $controller->cardStore();
});

$router->post('/mypage/update', function() {
    $controller = new MyPageController;
    $controller->update();
});

$router->post('/mypage/deactivate', function() {
    $controller = new MyPageController;
    $controller->deactivate();
});


$router->get('/mypage/password', function() {
    $controller = new MyPageController;
    $controller->editPassword();
});

$router->post('/mypage/password', function() {
    $controller = new MyPageController;
    $controller->updatePassword();
});

// パスワードリセット
$router->get('/forgot-password', function() {
    $controller = new AuthController();
    $controller->showForgotPasswordForm();
});

$router->post('/forgot-password', function() {
    $controller = new AuthController();
    $controller->sendResetLink();
});

// カート関連
$router->get('/cart', function() {
    $controller = new CartController();
    $controller->index();
});

$router->post('/cart/add', function() {
    $controller = new CartController();
    $controller->add();
});

$router->post('/cart/update', function() {
    $controller = new CartController();
    $controller->update();
});

$router->post('/cart/remove', function() {
    $controller = new CartController();
    $controller->remove();
});

$router->post('/cart/clear', function() {
    $controller = new CartController();
    $controller->clear();
});

$router->post('/cart/order', function() {
    $controller = new CartController();
    $controller->order();
});

// お気に入り関連
$router->get('/favorites', function() {
    $controller = new FavoriteController();
    $controller->list();
});

$router->post('/favorites/add', function() {
    $controller = new FavoriteController();
    $controller->add();
});

$router->post('/favorites/remove', function() {
    $controller = new FavoriteController();
    $controller->remove();
});

// 管理画面関連
$router->before('GET|POST', '/admin/.*', function() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: /login');
        exit();
    }
});

// 管理画面ダッシュボード
$router->get('/admin', function() {
    $controller = new AdminDashboardController();
    $controller->index();
});

$router->get('/admin/dashboard', function() {
    $controller = new AdminDashboardController();
    $controller->index();
});

$router->get('/api/admin/dashboard/stats', function() {
    $controller = new AdminDashboardController();
    $controller->getStats();
});

// 管理画面商品管理
$router->get('/admin/products', function() {
    $controller = new AdminProductController();
    $controller->index();
});

$router->get('/admin/products/create', function() {
    $controller = new AdminProductController();
    $controller->create();
});

$router->post('/admin/products/store', function() {
    $controller = new AdminProductController();
    $controller->store();
});

$router->get('/admin/products/edit/(\d+)', function($id) {
    $controller = new AdminProductController();
    $controller->edit($id);
});

$router->post('/admin/products/update/(\d+)', function($id) {
    $controller = new AdminProductController();
    $controller->update($id);
});

$router->post('/admin/products/delete/(\d+)', function($id) {
    $controller = new AdminProductController();
    $controller->delete($id);
});

$router->post('/admin/products/bulk-delete', function() {
    $controller = new AdminProductController();
    $controller->bulkDelete();
});

$router->post('/admin/products/import-csv', function() {
    $controller = new AdminProductController();
    $controller->importCsv();
});

$router->get('/admin/products/export-csv', function() {
    $controller = new AdminProductController();
    $controller->exportCsv();
});

// 管理画面注文管理
$router->get('/admin/orders', function() {
    $controller = new AdminOrderController();
    $controller->index();
});

$router->get('/admin/orders/search', function() {
    $controller = new AdminOrderController();
    $controller->search();
});

$router->get('/admin/orders/(\d+)', function($id) {
    $controller = new AdminOrderController();
    $controller->show($id);
});

$router->post('/admin/orders/delete', function() {
    $controller = new AdminOrderController();
    $controller->delete();
});

// 管理画面ユーザー管理
$router->get('/admin/users', function() {
    $controller = new AdminUserController();
    $controller->index();
});

$router->get('/admin/users/create', function() {
    $controller = new AdminUserController();
    $controller->create();
});

$router->post('/admin/users/store', function() {
    $controller = new AdminUserController();
    $controller->store();
});

$router->get('/admin/users/edit/(\d+)', function($id) {
    $controller = new AdminUserController();
    $controller->edit($id);
});

$router->post('/admin/users/update/(\d+)', function($id) {
    $controller = new AdminUserController();
    $controller->update($id);
});

$router->post('/admin/users/delete/(\d+)', function($id) {
    $controller = new AdminUserController();
    $controller->delete($id);
});

$router->get('/admin/users/orders/(\d+)', function($id) {
    $controller = new AdminUserController();
    $controller->showOrders($id);
});

// 404エラーハンドラー
$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    require __DIR__ . '/../View/errors/404.php';
});

return $router;