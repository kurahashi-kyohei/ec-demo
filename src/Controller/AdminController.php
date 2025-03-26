<?php

namespace App\Controller;

use App\Model\Product;
use App\Model\Order;
use App\Model\User;
use App\Middleware\SessionMiddleware;

class AdminController {
    private $productModel;
    private $orderModel;

    public function __construct() {
        SessionMiddleware::start();
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->userModel = new User();
    }

    public function dashboard() {
        $totalProducts = $this->productModel->getTotalCount();
        $totalOrders = $this->orderModel->getTotalCount();
        $totalUsers = $this->userModel->getTotalCount();

        require __DIR__ . '/../View/admin/dashboard.php';
    }

    public function productList() {
        $products = $this->productModel->getAllProducts();
        require __DIR__ . '/../View/admin/products/index.php';
    }

    public function showCreateProduct() {
        require __DIR__ . '/../View/admin/products/create.php';
    }

    public function createProduct() {
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_INT);
        $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

        // 画像のアップロード処理
        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/products/';
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $imagePath = '/uploads/products/' . $filename;
            }
        }

        if ($this->productModel->create([
            'name' => $name,
            'price' => $price,
            'stock' => $stock,
            'description' => $description,
            'image_path' => $imagePath
        ])) {
            $_SESSION['success'] = '商品を登録しました。';
        } else {
            $_SESSION['error'] = '商品の登録に失敗しました。';
        }

        header('Location: /admin/products');
    }

    public function showEditProduct($id) {
        $product = $this->productModel->findById($id);
        if (!$product) {
            header('Location: /admin/products');
            return;
        }
        require __DIR__ . '/../View/admin/products/edit.php';
    }

    public function editProduct($id) {
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_INT);
        $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

        $data = [
            'name' => $name,
            'price' => $price,
            'stock' => $stock,
            'description' => $description
        ];

        // 新しい画像がアップロードされた場合
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/products/';
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $data['image_path'] = '/uploads/products/' . $filename;
            }
        }

        if ($this->productModel->update($id, $data)) {
            $_SESSION['success'] = '商品情報を更新しました。';
        } else {
            $_SESSION['error'] = '商品情報の更新に失敗しました。';
        }

        header('Location: /admin/products');
    }

    public function deleteProduct($id) {
        if ($this->productModel->delete($id)) {
            $_SESSION['success'] = '商品を削除しました。';
        } else {
            $_SESSION['error'] = '商品の削除に失敗しました。';
        }
        header('Location: /admin/products');
    }

    public function orderList() {
        $orders = $this->orderModel->getAllOrders();
        require __DIR__ . '/../View/admin/orders/index.php';
    }

    public function orderDetail($id) {
        $order = $this->orderModel->getOrderWithDetails($id);
        if (!$order) {
            header('Location: /admin/orders');
            return;
        }
        require __DIR__ . '/../View/admin/orders/detail.php';
    }

    public function updateOrderStatus($id) {
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
        
        if ($this->orderModel->updateStatus($id, $status)) {
            $_SESSION['success'] = '注文ステータスを更新しました。';
        } else {
            $_SESSION['error'] = '注文ステータスの更新に失敗しました。';
        }
        
        header('Location: /admin/orders/' . $id);
    }
} 