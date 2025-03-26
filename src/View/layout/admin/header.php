<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理画面 - <?= $title ?? 'ルアーショップ' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body class="admin">
    <header class="admin-header">
        <div class="admin-header__container">
            <a href="/admin" class="admin-header__logo">
                管理画面
            </a>
            <button class="admin-header__menu-button" id="menuButton" aria-label="メニュー">
                <i class="fas fa-bars"></i>
            </button>
            <nav class="admin-nav" id="adminNav">
                <ul class="admin-nav__list">
                    <li class="admin-nav__item">
                        <a href="/admin" class="admin-nav__link <?= $_SERVER['REQUEST_URI'] === '/admin' ? 'admin-nav__link--active' : '' ?>">
                            <i class="fas fa-home"></i>
                            <span>ダッシュボード</span>
                        </a>
                    </li>
                    <li class="admin-nav__item">
                        <a href="/admin/products" class="admin-nav__link <?= strpos($_SERVER['REQUEST_URI'], '/admin/products') === 0 ? 'admin-nav__link--active' : '' ?>">
                            <i class="fas fa-box"></i>
                            <span>商品管理</span>
                        </a>
                    </li>
                    <li class="admin-nav__item">
                        <a href="/admin/orders" class="admin-nav__link <?= strpos($_SERVER['REQUEST_URI'], '/admin/orders') === 0 ? 'admin-nav__link--active' : '' ?>">
                            <i class="fas fa-shopping-cart"></i>
                            <span>注文管理</span>
                        </a>
                    </li>
                    <li class="admin-nav__item">
                        <a href="/admin/users" class="admin-nav__link <?= strpos($_SERVER['REQUEST_URI'], '/admin/users') === 0 ? 'admin-nav__link--active' : '' ?>">
                            <i class="fas fa-users"></i>
                            <span>ユーザー管理</span>
                        </a>
                    </li>
                    <li class="admin-nav__item admin-nav__item--mobile">
                        <form action="/logout" method="post">
                            <button type="submit" class="admin-nav__logout-button">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>ログアウト</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
            <div class="admin-header__user">
                <span class="admin-header__user-name">
                    <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>
                </span>
                <form action="/logout" method="post" class="admin-header__logout">
                    <button type="submit" class="admin-header__logout-button">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>ログアウト</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="admin-content">
        <div class="admin-content__container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert--success">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert--error">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuButton = document.getElementById('menuButton');
            const adminNav = document.getElementById('adminNav');
            
            menuButton.addEventListener('click', function() {
                adminNav.classList.toggle('admin-nav--active');
                menuButton.classList.toggle('admin-header__menu-button--active');
            });
        });
    </script> 