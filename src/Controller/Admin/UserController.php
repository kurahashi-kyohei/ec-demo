<?php

namespace App\Controller\Admin;

use App\Model\User;
use App\Middleware\SessionMiddleware;

class UserController
{
    private $userModel;

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

        $this->userModel = new User();
    }

    public function index()
    {
        $users = $this->userModel->getAllUsers();
        
        $data = [
            'title' => 'ユーザー管理',
            'users' => $users
        ];

        extract($data);
        require __DIR__ . '/../../View/admin/users/index.php';
    }

    public function create()
    {
        $data = [
            'title' => 'ユーザー登録'
        ];

        extract($data);
        require __DIR__ . '/../../View/admin/users/create.php';
    }

    public function store()
    {
        $email = $_POST['email'] ?? '';
        $phoneNumber = $_POST['phone_number'] ?? '';
        $address = $_POST['address'] ?? '';
        $password = $_POST['password'] ?? '';
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $status = $_POST['status'] ?? 'active';

        if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
            $_SESSION['error'] = '必須項目を入力してください。';
            header('Location: /admin/users/create');
            exit();
        }

        if ($this->userModel->findByEmail($email)) {
            $_SESSION['error'] = 'このメールアドレスは既に使用されています。';
            header('Location: /admin/users/create');
            exit();
        }

        $result = $this->userModel->create([
            'email' => $email,
            'password' => $password,
            'phone_number' => $phoneNumber,
            'address' => $address,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $role,
            'status' => $status
        ]);

        if ($result) {
            $_SESSION['success'] = 'ユーザーを登録しました。';
            header('Location: /admin/users');
            exit();
        } else {
            $_SESSION['error'] = 'ユーザーの登録に失敗しました。';
            header('Location: /admin/users/create');
            exit();
        }
    }

    public function edit($id)
    {
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'ユーザーが見つかりませんでした。';
            header('Location: /admin/users');
            exit();
        }

        $data = [
            'title' => 'ユーザー編集',
            'user' => $user
        ];

        extract($data);
        require __DIR__ . '/../../View/admin/users/edit.php';
    }

    public function update($id)
    {
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'ユーザーが見つかりませんでした。';
            header('Location: /admin/users');
            exit();
        }

        $email = $_POST['email'] ?? '';
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $phoneNumber = $_POST['phone_number'] ?? '';
        $address = $_POST['address'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $status = $_POST['status'] ?? 'active';

        // バリデーション
        if (empty($email) || empty($firstName) || empty($lastName)) {
            $_SESSION['error'] = '必須項目を入力してください。';
            header('Location: /admin/users/edit/' . $id);
            exit();
        }

        // メールアドレスの重複チェック（現在のユーザーを除く）
        $existingUser = $this->userModel->findByEmail($email);
        if ($existingUser && $existingUser['id'] != $id) {
            $_SESSION['error'] = 'このメールアドレスは既に使用されています。';
            header('Location: /admin/users/edit/' . $id);
            exit();
        }

        $result = $this->userModel->update($id, [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone_number' => $phoneNumber,
            'address' => $address,
            'role' => $role,
            'status' => $status,
        ]);

        if ($result) {
            $_SESSION['success'] = 'ユーザー情報を更新しました。';
            header('Location: /admin/users');
            exit();
        } else {
            $_SESSION['error'] = 'ユーザー情報の更新に失敗しました。';
            header('Location: /admin/users/edit/' . $id);
            exit();
        }
    }

    public function delete($id)
    {
        // 自分自身は削除できない
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = '自分自身は削除できません。';
            header('Location: /admin/users');
            exit();
        }

        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'ユーザーが見つかりませんでした。';
            header('Location: /admin/users');
            exit();
        }

        $result = $this->userModel->delete($id);

        if ($result) {
            $_SESSION['success'] = 'ユーザーを削除しました。';
        } else {
            $_SESSION['error'] = 'ユーザーの削除に失敗しました。';
        }

        header('Location: /admin/users');
        exit();
    }

    public function show($id)
    {
        $user = $this->userModel->getUserWithOrders($id);
        
        if (!$user) {
            $_SESSION['error'] = 'ユーザーが見つかりませんでした。';
            header('Location: /admin/users');
            exit();
        }

        $data = [
            'title' => 'ユーザー詳細',
            'user' => $user
        ];

        extract($data);
        require __DIR__ . '/../../View/admin/users/detail.php';
    }

    public function toggleStatus($id)
    {
        // 管理者のステータスは変更できない
        $user = $this->userModel->findById($id);
        if (!$user || $user['role'] === 'admin') {
            $_SESSION['error'] = 'ユーザーのステータスを変更できません。';
            header('Location: /admin/users');
            exit();
        }

        $result = $this->userModel->toggleStatus($id);

        if ($result) {
            $_SESSION['success'] = 'ユーザーのステータスを更新しました。';
        } else {
            $_SESSION['error'] = 'ユーザーのステータスの更新に失敗しました。';
        }

        header('Location: /admin/users');
        exit();
    }

    public function showOrders($id)
    {
        $orders = $this->userModel->getOrdersByUserId($id);
        $data = [
            'title' => '注文履歴',
            'orders' => $orders 
        ];
        
        extract($data);
        require __DIR__ . '/../../View/admin/users/orders.php';
    }
    
} 