<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>渓流ルアー専門店</title>
    <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <a href="/" class="header__logo">
                <h1>渓流ルアー専門店</h1>
            </a>
            <nav class="header__nav">
                <ul class="header__menu">
                    <li><a href="/" class="header__menu-item">ホーム</a></li>
                    <li><a href="/products" class="header__menu-item">商品一覧</a></li>
                    <li><a href="/cart" class="header__menu-item">カート</a></li>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li><a href="/login" class="header__menu-item">ログイン</a></li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="/login" class="header__menu-item">マイページ</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <button class="header__menu-button" aria-label="メニュー">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
    <div class="content-wrapper"> 