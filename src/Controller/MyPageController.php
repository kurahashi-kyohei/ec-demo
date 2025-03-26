<?php

namespace App\Controller;

use App\Model\User;
use App\Middleware\SessionMiddleware;

class MyPageController {
    private $userModel;

    public function __construct() {
        SessionMiddleware::start();
        SessionMiddleware::requireLogin();
        $this->userModel = new User();
    }

    public function index() {
        $user = $this->userModel->findById($_SESSION['user_id']);
        require __DIR__ . '/../View/mypage/index.php';
    }

    public function edit() {
        $user = $this->userModel->findById($_SESSION['user_id']);
        require __DIR__ . '/../View/mypage/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /mypage/edit');
            return;
        }

        // 現在のユーザー情報を取得
        $currentUser = $this->userModel->findById($_SESSION['user_id']);

        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'role' => $currentUser['role'],  // 既存のroleを保持
            'status' => $currentUser['status']  // 既存のstatusを保持
        ];

        // バリデーション
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
            $_SESSION['error'] = '全ての項目を入力してください。';
            header('Location: /mypage/edit');
            return;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = '有効なメールアドレスを入力してください。';
            header('Location: /mypage/edit');
            return;
        }

        if ($this->userModel->update($_SESSION['user_id'], $data)) {
            $_SESSION['success'] = 'プロフィールを更新しました。';
            header('Location: /mypage');
        } else {
            $_SESSION['error'] = 'プロフィールの更新に失敗しました。';
            header('Location: /mypage/edit');
        }
    }

    public function editPassword() {
        $user = $this->userModel->findById($_SESSION['user_id']);
        require __DIR__ . '/../View/mypage/password.php';
    }

    public function updatePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /mypage/password');
            return;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $newPasswordConfirmation = $_POST['new_password_confirmation'] ?? '';

        $user = $this->userModel->findById($_SESSION['user_id']);

        // バリデーション
        if (!$this->userModel->verifyPassword($currentPassword, $user['password'])) {
            $_SESSION['error'] = '現在のパスワードが正しくありません。';
            header('Location: /mypage/password');
            return;
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['error'] = '新しいパスワードは8文字以上で入力してください。';
            header('Location: /mypage/password');
            return;
        }

        if ($newPassword !== $newPasswordConfirmation) {
            $_SESSION['error'] = '新しいパスワードと確認用パスワードが一致しません。';
            header('Location: /mypage/password');
            return;
        }

        if ($this->userModel->updatePassword($user['email'], $newPassword)) {
            $_SESSION['success'] = 'パスワードを更新しました。';
            header('Location: /mypage');
        } else {
            $_SESSION['error'] = 'パスワードの更新に失敗しました。';
            header('Location: /mypage/password');
        }
    }
} 