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
            require __DIR__ . '/../View/auth/register.php';
            return;
        }

        $data = [
            'email' => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
            'password' => $_POST['password'] ?? '',
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'phone_number' => $_POST['phone_number'] ?? '',
            'address' => $_POST['address'] ?? ''
        ];

        // バリデーション
        $errors = [];

        if (!$data['email']) {
            $errors[] = 'メールアドレスの形式が正しくありません。';
        }

        if (strlen($data['password']) < 8 || !preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $data['password'])) {
            $errors[] = 'パスワードは8文字以上で、文字と数字を含める必要があります。';
        }

        if (empty($data['first_name'])) {
            $errors[] = '名を入力してください。';
        }

        if (empty($data['last_name'])) {
            $errors[] = '姓を入力してください。';
        }

        if (empty($data['phone_number'])) {
            $errors[] = '電話番号は必須です。';
        } elseif (!preg_match('/^[0-9-]{10,}$/', str_replace('-', '', $data['phone_number']))) {
            $errors[] = '正しい電話番号の形式で入力してください。';
        }

        // メールアドレスの重複チェック
        if ($this->userModel->findByEmail($data['email'])) {
            $errors[] = 'このメールアドレスは既に登録されています。';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            require __DIR__ . '/../View/auth/register.php';
            return;
        }

        // ユーザー登録
        $result = $this->userModel->create([
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone_number' => $data['phone_number'],
            'address' => $data['address']
        ]);

        if ($result) {
            $_SESSION['success'] = '登録が完了しました。';
            header('Location: /login');
            exit;
        } else {
            $_SESSION['error'] = '登録に失敗しました。';
            require __DIR__ . '/../View/auth/register.php';
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
        if ($user['status'] === '無効') {
            $_SESSION['error'] = 'このメールアドレスは無効です。';
            header('Location: /login');
            return;
        }
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