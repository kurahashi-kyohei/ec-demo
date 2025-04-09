<?php

namespace App\Controller;

use App\Model\Cart;
use App\Model\Product;
use App\Middleware\SessionMiddleware;

class CartController {
    private $cartModel;
    private $productModel;

    public function __construct() {
        SessionMiddleware::start();
        $this->cartModel = new Cart();
        $this->productModel = new Product();
    }

    public function index() {
        $cartItems = [];
        $items = $this->cartModel->getItems();
        
        foreach ($items as $productId => $quantity) {
            $product = $this->productModel->getProductById($productId);
            if ($product) {
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $product['price'] * $quantity
                ];
            }
        }

        $total = $this->cartModel->getTotal();
        require __DIR__ . '/../View/cart/index.php';
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /cart');
            return;
        }

        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        if (!$productId || !$quantity || $quantity < 1) {
            $_SESSION['error'] = '無効なリクエストです。';
            header('Location: /cart');
            return;
        }

        $product = $this->productModel->getProductById($productId);
        if (!$product) {
            $_SESSION['error'] = '商品が見つかりません。';
            header('Location: /cart');
            return;
        }

        if ($product['stock'] < $quantity) {
            $_SESSION['error'] = '在庫が不足しています。';
            header('Location: /products/' . $productId);
            return;
        }

        $this->cartModel->add($productId, $quantity);
        $_SESSION['success'] = 'カートに商品を追加しました。';
        header('Location: /cart');
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /cart');
            return;
        }

        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        if (!$productId || !$quantity) {
            $_SESSION['error'] = '無効なリクエストです。';
            header('Location: /cart');
            return;
        }

        $product = $this->productModel->getProductById($productId);
        if (!$product) {
            $_SESSION['error'] = '商品が見つかりません。';
            header('Location: /cart');
            return;
        }

        if ($product['stock'] < $quantity) {
            $_SESSION['error'] = '在庫が不足しています。';
            header('Location: /cart');
            return;
        }

        $this->cartModel->update($productId, $quantity);
        $_SESSION['success'] = 'カートを更新しました。';
        header('Location: /cart');
    }

    public function remove() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /cart');
            return;
        }

        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if (!$productId) {
            $_SESSION['error'] = '無効なリクエストです。';
            header('Location: /cart');
            return;
        }

        $this->cartModel->remove($productId);
        $_SESSION['success'] = '商品をカートから削除しました。';
        header('Location: /cart');
    }

    public function clear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /cart');
            return;
        }

        $this->cartModel->clear();
        $_SESSION['success'] = 'カートを空にしました。';
        header('Location: /cart');
    }

    public function order() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /cart');
            return;
        }

        $this->cartModel->order();
        $_SESSION['success'] = '注文が確定されました。';
        header('Location: /cart');
    }
} 