<?php

namespace App\Controller;

use App\Model\User;
use Exception;

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

    public function update()
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error'] = 'ログインが必要です。';
                header('Location: /login');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('不正なリクエストです。');
            }

            $userId = $_SESSION['user_id'];
            $currentPassword = $_POST['current_password'];

            // 現在のパスワードの検証
            $user = new User();
            if (!$user->verifyPassword($userId, $currentPassword)) {
                throw new Exception('現在のパスワードが正しくありません。');
            }

            // 更新データの準備
            $updateData = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'] ?? null,
                'address' => $_POST['address'] ?? null
            ];

            // クレジットカード情報の処理
            if (!empty($_POST['card_number'])) {
                // カード番号のフォーマットチェック
                if (!preg_match('/^\d{4}-\d{4}-\d{4}-\d{4}$/', $_POST['card_number'])) {
                    throw new Exception('カード番号の形式が正しくありません。');
                }
                
                // 有効期限のフォーマットチェック
                if (!empty($_POST['card_expiry']) && !preg_match('/^\d{2}\/\d{4}$/', $_POST['card_expiry'])) {
                    throw new Exception('有効期限の形式が正しくありません。');
                }

                // カード番号の暗号化（実際の運用では適切な暗号化方式を使用してください）
                $cardNumber = str_replace('-', '', $_POST['card_number']);
                $updateData['card_number'] = $cardNumber;
                $updateData['card_holder'] = $_POST['card_holder'];
                $updateData['card_expiry'] = $_POST['card_expiry'];
                $updateData['card_brand'] = $_POST['card_brand'];
            }

            // 新しいパスワードがある場合は更新
            if (!empty($_POST['new_password'])) {
                if (strlen($_POST['new_password']) < 8) {
                    throw new Exception('パスワードは8文字以上で設定してください。');
                }
                $updateData['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            }

            // ユーザー情報の更新
            if ($user->updateUser($userId, $updateData)) {
                $_SESSION['success'] = 'プロフィールを更新しました。';
            } else {
                throw new Exception('プロフィールの更新に失敗しました。');
            }

            header('Location: /users/profile');
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /users/edit');
            exit;
        }
    }
} 