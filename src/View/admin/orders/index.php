<?php require __DIR__ . '/../../layout/admin/header.php'; ?>

<div class="admin-orders">
    <div class="admin-orders__container">
        <div class="admin-orders__header">
            <h1 class="admin-orders__title">注文管理</h1>
        </div>

        <?php if (empty($orders)): ?>
            <div class="admin-orders__empty">
                <p>注文がありません。</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>注文番号</th>
                            <th>注文者</th>
                            <th>合計金額</th>
                            <th>ステータス</th>
                            <th>注文日時</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['user_name']) ?></td>
                                <td>¥<?= number_format($order['total_amount']) ?></td>
                                <td>
                                    <span class="status-badge status-badge--<?= strtolower($order['status']) ?>">
                                        <?= htmlspecialchars($order['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('Y/m/d H:i', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <a href="/admin/orders/<?= $order['id'] ?>" 
                                       class="button button--small button--secondary">
                                        <i class="fas fa-eye"></i>
                                        詳細
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
