<?php

namespace App\Model;

class Cart {
    public function __construct() {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function add($productId, $quantity) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
    }

    public function update($productId, $quantity) {
        if ($quantity > 0) {
            $_SESSION['cart'][$productId] = $quantity;
        } else {
            $this->remove($productId);
        }
    }

    public function remove($productId) {
        unset($_SESSION['cart'][$productId]);
    }

    public function clear() {
        $_SESSION['cart'] = [];
    }

    public function getItems() {
        return $_SESSION['cart'];
    }

    public function getCount() {
        return array_sum($_SESSION['cart']);
    }

    public function getTotal() {
        $total = 0;
        $productModel = new Product();

        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $product = $productModel->getProductById($productId);
            if ($product) {
                $total += $product['price'] * $quantity;
            }
        }

        return $total;
    }
} 