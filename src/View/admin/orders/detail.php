<?php require __DIR__ . '/../../layout/admin/header.php'; ?>

<div class="admin-order-detail">
    <div class="admin-order-detail__container">
        <div class="admin-order-detail__header">
            <h1 class="admin-order-detail__title">注文詳細 #<?= $order['id'] ?></h1>
            <a href="/admin/orders" class="button button--secondary">
                <i class="fas fa-arrow-left"></i>
                注文一覧に戻る
            </a>
        </div>

        <div class="admin-order-detail__content">
            
            <div class="order-info">
                <h2 class="order-info__title">注文情報</h2>
                <div class="order-info__grid">
                    <div class="order-info__item">
                        <span class="order-info__label">注文者</span>
                        <span class="order-info__value"><?= htmlspecialchars($order['user_name']) ?></span>
                    </div>
                    <div class="order-info__item">
                        <span class="order-info__label">メールアドレス</span>
                        <span class="order-info__value"><?= htmlspecialchars($order['email']) ?></span>
                    </div>
                    <div class="order-info__item">
                        <span class="order-info__label">注文日時</span>
                        <span class="order-info__value"><?= date('Y/m/d H:i', strtotime($order['created_at'])) ?></span>
                    </div>
                </div>
            </div>

            <div class="order-items">
                <h2 class="order-items__title">注文商品</h2>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>商品</th>
                                <th>単価</th>
                                <th>数量</th>
                                <th>小計</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td class="order-item">
                                        <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>"
                                             class="order-item__image">
                                        <span class="order-item__name">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </span>
                                    </td>
                                    <td>¥<?= number_format($item['price']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>¥<?= number_format($item['price'] * $item['quantity']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right">合計</td>
                                <td class="total-amount">¥<?= number_format($order['total_amount']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

