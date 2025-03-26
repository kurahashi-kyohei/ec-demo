<?php

namespace App\Controller;

use App\Model\User;
use App\Middleware\SessionMiddleware;

class AuthController {
    private $userModel;

    public function __construct() {
        SessionMiddleware::start();
        $this->userModel = new User();
    }

    public function showLoginForm() {
        if (SessionMiddleware::isLoggedIn()) {
            header('Location: /mypage');
            exit;
        }
        require __DIR__ . '/../View/auth/login.php';
    }

    public function showRegisterForm() {
        if (SessionMiddleware::isLoggedIn()) {
            header('Location: /mypage');
            exit;
        }
        require __DIR__ . '/../View/auth/register.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /register');
            return;
        }

        $data = [
            'email' => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
            'password' => $_POST['password'] ?? '',
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? ''
        ];

        // バリデーション
        if (!$data['email'] || strlen($data['password']) < 8 || 
            empty($data['first_name']) || empty($data['last_name'])) {
            $_SESSION['error'] = '入力内容に誤りがあります。';
            header('Location: /register');
            return;
        }

        // メールアドレスの重複チェック
        if ($this->userModel->findByEmail($data['email'])) {
            $_SESSION['error'] = 'このメールアドレスは既に登録されています。';
            header('Location: /register');
            return;
        }

        if ($this->userModel->create($data)) {
            $_SESSION['success'] = '登録が完了しました。';
            header('Location: /login');
        } else {
            $_SESSION['error'] = '登録に失敗しました。';
            header('Location: /register');
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            return;
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email || empty($password)) {
            $_SESSION['error'] = 'メールアドレスとパスワードを入力してください。';
            header('Location: /login');
            return;
        }

        $user = $this->userModel->findByEmail($email);
        if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
            SessionMiddleware::regenerate(); // セッションIDを再生成
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['last_name'] . ' ' . $user['first_name'];
            $_SESSION['role'] = $user['role']; // ロールをセッションに保存
            header('Location: /mypage');
        } else {
            $_SESSION['error'] = 'メールアドレスまたはパスワードが正しくありません。';
            header('Location: /login');
        }
    }

    public function logout() {
        session_destroy();
        header('Location: /');
        exit;
    }

    public function showForgotPasswordForm() {
        require __DIR__ . '/../View/auth/forgot-password.php';
    }

    public function sendResetLink() {
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $_SESSION['error'] = '有効なメールアドレスを入力してください。';
            header('Location: /forgot-password');
            return;
        }

        $user = $this->userModel->findByEmail($email);
        if ($user) {
            $token = $this->userModel->createPasswordReset($email);
            // TODO: メール送信処理を実装
            $_SESSION['success'] = 'パスワードリセットのリンクをメールで送信しました。';
        } else {
            $_SESSION['error'] = 'このメールアドレスは登録されていません。';
        }
        header('Location: /forgot-password');
    }
} 