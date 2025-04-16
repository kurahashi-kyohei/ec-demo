<?php

namespace App\Controller;

use App\Model\User;
use App\Middleware\SessionMiddleware;
use Google_Client;
use Google_Service_Oauth2;

class AuthController {
    private $userModel;
    private $client;

    public function __construct() {
        SessionMiddleware::start();
        $this->userModel = new User();

        $clientId = $_ENV['GOOGLE_CLIENT_ID'];
        $clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];
        $appUrl = $_ENV['APP_URL'];

        $this->client = new Google_Client();
        $this->client->setClientId($clientId);
        $this->client->setClientSecret($clientSecret);
        
        $redirectUri = rtrim($appUrl, '/') . '/auth/google/callback';
        $this->client->setRedirectUri($redirectUri);
        
        $this->client->addScope('email');

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

    private function verifyRecaptcha($recaptchaResponse, $action) {
        if (empty($recaptchaResponse)) {
            error_log('reCAPTCHA Error: Empty response');
            return false;
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $_ENV['RECAPTCHA_SECRET_KEY'],
            'response' => $recaptchaResponse
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        try {
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            $response = json_decode($result);

            // デバッグ情報の出力
            error_log('reCAPTCHA Response: ' . print_r($response, true));

            if (!$response->success) {
                error_log('reCAPTCHA Error: ' . json_encode($response));
                return false;
            }

            // スコアの検証（0.3以上を有効とする - 開発環境用に閾値を下げる）
            if ($response->score < 0.3) {
                error_log('reCAPTCHA Score Too Low: ' . $response->score);
                return false;
            }

            return true;

        } catch (\Exception $e) {
            error_log('reCAPTCHA Verification Error: ' . $e->getMessage());
            return false;
        }
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require __DIR__ . '/../View/auth/register.php';
            return;
        }

        // reCAPTCHA v3の検証
        $recaptchaResponse = $_POST['recaptcha_response'] ?? '';
        if (!$this->verifyRecaptcha($recaptchaResponse, 'register')) {
            $_SESSION['error'] = '不正なアクセスと判断されました。もう一度お試しください。';
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
        
        if (!$user) {
            $_SESSION['error'] = 'メールアドレスまたはパスワードが正しくありません。';
            header('Location: /login');
            return;
        }

        if ($user['status'] === '無効') {
            $_SESSION['error'] = 'このアカウントは無効になっています。';
            header('Location: /login');
            return;
        }

        if ($this->userModel->verifyPassword($password, $user['password'])) {
            SessionMiddleware::regenerate();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['last_name'] . ' ' . $user['first_name'];
            $_SESSION['role'] = $user['role'];
            header('Location: /mypage');
            exit;
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

    public function redirectToGoogle()
    {
        try {            
            $authUrl = $this->client->createAuthUrl();
            header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Google認証の準備中にエラーが発生しました：' . $e->getMessage();
            header('Location: /login');
            exit;
        }
    }

    public function handleGoogleCallback()
    {
        try {
            if (!isset($_GET['code'])) {
                throw new \Exception('認証コードが見つかりません。');
            }

            $token = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
            
            if (isset($token['error'])) {
                throw new \Exception($token['error_description'] ?? 'トークンの取得に失敗しました。');
            }

            $this->client->setAccessToken($token);

            $google_oauth = new Google_Service_Oauth2($this->client);
            $google_account_info = $google_oauth->userinfo->get();

            $email = $google_account_info->email;
            $user = $this->userModel->findByEmail($email);

            if (!$user) {
                $userData = [
                    'email' => $email,
                    'first_name' => $google_account_info->givenName ?? '',
                    'last_name' => $google_account_info->familyName ?? '',
                    'password' => bin2hex(random_bytes(32)),
                    'is_social_login' => true
                ];

                $userId = $this->userModel->createUser($userData);
                $user = $this->userModel->findById($userId);
            }

            SessionMiddleware::regenerate();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['last_name'] . ' ' . $user['first_name'];
            $_SESSION['role'] = $user['role'];

            header('Location: /mypage');
            exit;

        } catch (\Exception $e) {
            error_log('Google OAuth Error: ' . $e->getMessage());
            $_SESSION['error'] = 'Googleログインに失敗しました：' . $e->getMessage();
            header('Location: /login');
            exit;
        }
    }
} 
