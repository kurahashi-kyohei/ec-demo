<?php

namespace App\Controller;

use App\Model\User;

class UserController
{
    private $userModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // ログインチェック
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        $this->userModel = new User();
    }

    // ... 既存のメソッド ...

    public function deactivate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /mypage');
            exit();
        }

        $userId = $_SESSION['user_id'];
        
        // ユーザーのステータスを無効に更新
        $result = $this->userModel->update($userId, [
            'status' => 'inactive'
        ]);

        if ($result) {
            // セッションを破棄してログアウト
            session_destroy();
            
            // ログインページにリダイレクト
            header('Location: /login');
            exit();
        } else {
            $_SESSION['error'] = '退会処理に失敗しました。';
            header('Location: /mypage');
            exit();
        }
    }
} 