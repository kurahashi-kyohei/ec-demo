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
                    <form action="/admin/orders/search" method="get">
                        <input type="text" name="keyword">
                        <button type="submit" class="button button--secondary">
                            <i class="fas fa-search"></i>
                            検索
                        </button>
                    </form>
                    <thead>
                        <tr>
                            <th>注文番号</th>
                            <th>注文者</th>
                            <th>合計金額</th>
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
                                <td><?= date('Y/m/d H:i', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <a href="/admin/orders/<?= $order['id'] ?>" 
                                       class="button button--small button--secondary">
                                        <i class="fas fa-eye"></i>
                                        詳細
                                    </a>
                                    <form action="/admin/orders/delete" method="POST" class="form--inline">
                                        <input type="hidden" name="id" value="<?= $order['id'] ?>">
                                        <button type="submit" class="button button--small button--danger">
                                            <i class="fas fa-trash"></i>
                                            削除
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
