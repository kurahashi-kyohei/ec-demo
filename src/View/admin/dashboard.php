<?php require __DIR__ . '/../layout/admin/header.php'; ?>

<div class="dashboard">
    <h1 class="dashboard__title">ダッシュボード</h1>

    <div class="dashboard__stats">
        <div class="dashboard__stat-card">
            <div>
                <h3>総商品数</h3>
                <p class="dashboard__stat-number"><?= number_format($totalProducts) ?></p>
            </div>
            <button class="dashboard__stat-button"><a href="/admin/products">商品管理へ</a></button>
        </div>
        <div class="dashboard__stat-card">
            <div>
                <h3>総注文数</h3>
                <p class="dashboard__stat-number"><?= number_format($totalOrders) ?></p>
            </div>
            <button class="dashboard__stat-button"><a href="/admin/orders">注文管理へ</a></button>
        </div>
        <div class="dashboard__stat-card">
            <div>
                <h3>ユーザー数</h3>
                <p class="dashboard__stat-number"><?= number_format($totalUsers) ?></p>
            </div>
            <button class="dashboard__stat-button"><a href="/admin/users">ユーザー管理へ</a></button>
        </div>
    </div>
</div>
