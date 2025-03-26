<?php

namespace App\Controller\Admin;

use App\Model\Product;
use App\Middleware\SessionMiddleware;
use App\Util\ImageUploader;

class ProductController
{
    private $productModel;
    private $imageUploader;

    public function __construct()
    {
        // セッションが開始されていない場合は開始
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 管理者権限チェック
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit();
        }

        $this->productModel = new Product();
        $this->imageUploader = new ImageUploader();
    }

    public function index()
    {
        $products = $this->productModel->getAllProducts();
        
        $data = [
            'title' => '商品管理',
            'products' => $products
        ];

        extract($data);
        require __DIR__ . '/../../View/admin/products/index.php';
    }

    public function create()
    {
        $data = [
            'title' => '商品登録'
        ];

        extract($data);
        require __DIR__ . '/../../View/admin/products/create.php';
    }

    public function store()
    {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $category = $_POST['category'] ?? '';

        // バリデーション
        if (empty($name) || empty($description) || empty($category)) {
            $_SESSION['error'] = '必須項目を入力してください。';
            header('Location: /admin/products/create');
            exit();
        }

        // 画像のアップロード処理
        $imagePath = '';
        try {
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $fileName = $this->imageUploader->upload($_FILES['image'], $name);
                $imagePath = '/uploads/products/' . $fileName;
            }
        } catch (\RuntimeException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /admin/products/create');
            exit();
        }

        $result = $this->productModel->create([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'image_path' => $imagePath,
            'category' => $category
        ]);

        if ($result) {
            $_SESSION['success'] = '商品を登録しました。';
            header('Location: /admin/products');
            exit();
        } else {
            $_SESSION['error'] = '商品の登録に失敗しました。';
            header('Location: /admin/products/create');
            exit();
        }
    }

    public function edit($id)
    {
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            $_SESSION['error'] = '商品が見つかりませんでした。';
            header('Location: /admin/products');
            exit();
        }

        $data = [
            'title' => '商品編集',
            'product' => $product
        ];

        extract($data);
        require __DIR__ . '/../../View/admin/products/edit.php';
    }

    public function update($id)
    {
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            $_SESSION['error'] = '商品が見つかりませんでした。';
            header('Location: /admin/products');
            exit();
        }

        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $category = $_POST['category'] ?? '';

        // バリデーション
        if (empty($name) || empty($description) || empty($category)) {
            $_SESSION['error'] = '必須項目を入力してください。';
            header('Location: /admin/products/edit/' . $id);
            exit();
        }

        // 既存の画像パスを保持
        $imagePath = $product['image_path'];

        // 新しい画像がアップロードされた場合
        try {
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                // 古い画像を削除
                if (!empty($product['image_path'])) {
                    $oldFileName = basename($product['image_path']);
                    $this->imageUploader->delete($oldFileName);
                }

                $fileName = $this->imageUploader->upload($_FILES['image'], $name);
                $imagePath = '/uploads/products/' . $fileName;
            }
        } catch (\RuntimeException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /admin/products/edit/' . $id);
            exit();
        }

        $result = $this->productModel->update($id, [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'image_path' => $imagePath,
            'category' => $category
        ]);

        if ($result) {
            $_SESSION['success'] = '商品を更新しました。';
            header('Location: /admin/products');
            exit();
        } else {
            $_SESSION['error'] = '商品の更新に失敗しました。';
            header('Location: /admin/products/edit/' . $id);
            exit();
        }
    }

    public function delete($id)
    {
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            $_SESSION['error'] = '商品が見つかりませんでした。';
            header('Location: /admin/products');
            exit();
        }

        // 商品画像の削除
        if (!empty($product['image_path'])) {
            $fileName = basename($product['image_path']);
            $this->imageUploader->delete($fileName);
        }

        // 商品データの削除
        $result = $this->productModel->delete($id);

        if ($result) {
            $_SESSION['success'] = '商品を削除しました。';
        } else {
            $_SESSION['error'] = '商品の削除に失敗しました。';
        }

        header('Location: /admin/products');
        exit();
    }
} 