<?php

namespace App\Controller;

use App\Model\Favorite;
use App\Middleware\SessionMiddleware;

class FavoriteController {
    private $favoriteModel;

    public function __construct() {
        SessionMiddleware::start();
        $this->favoriteModel = new Favorite();
    }

    public function add() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'ログインが必要です。']);
            return;
        }

        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if (!$productId) {
            http_response_code(400);
            echo json_encode(['error' => '無効なリクエストです。']);
            return;
        }

        if ($this->favoriteModel->add($_SESSION['user_id'], $productId)) {
            $count = $this->favoriteModel->getFavoriteCount($productId);
            echo json_encode([
                'success' => true,
                'message' => 'お気に入りに追加しました。',
                'count' => $count
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'すでにお気に入りに追加されています。']);
        }
    }

    public function remove() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'ログインが必要です。']);
            return;
        }

        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if (!$productId) {
            http_response_code(400);
            echo json_encode(['error' => '無効なリクエストです。']);
            return;
        }

        if ($this->favoriteModel->remove($_SESSION['user_id'], $productId)) {
            $count = $this->favoriteModel->getFavoriteCount($productId);
            echo json_encode([
                'success' => true,
                'message' => 'お気に入りから削除しました。',
                'count' => $count
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'お気に入りから削除できませんでした。']);
        }
    }

    public function list() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            return;
        }

        $favorites = $this->favoriteModel->getUserFavorites($_SESSION['user_id']);
        require __DIR__ . '/../View/favorites/index.php';
    }
} 