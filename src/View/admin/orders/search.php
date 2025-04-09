<?php require __DIR__ . '/../../layout/admin/header.php'; ?>

<div class="admin-orders">
    <div class="admin-orders__container">
        <div class="admin-orders__header">
            <div class="admin-orders__header-inner">
                <h1 class="admin-orders__title">
                    <i class="fas fa-search"></i>
                    注文検索結果
                </h1>
                <div class="admin-orders__search">
                    <form action="/admin/orders/search" method="GET" class="search-form">
                        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" 
                               placeholder="注文者名、メールアドレス、注文番号で検索" 
                               class="search-form__input">
                        <button type="submit" class="button button--primary">
                            <i class="fas fa-search"></i>
                            検索
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <?php if (empty($orders)): ?>
            <div class="admin-orders__empty">
                <p>検索条件に一致する注文が見つかりませんでした。</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>注文番号</th>
                            <th>注文者</th>
                            <th>メールアドレス</th>
                            <th>商品数</th>
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
                                <td><?= htmlspecialchars($order['email']) ?></td>
                                <td><?= $order['item_count'] ?></td>
                                <td>¥<?= number_format($order['total_amount']) ?></td>
                                <td><?= date('Y/m/d H:i', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <div class="button-group">
                                        <a href="/admin/orders/<?= $order['id'] ?>" 
                                           class="button button--small button--secondary">
                                            <i class="fas fa-eye"></i>
                                            詳細
                                        </a>
                                        <form action="/admin/orders/delete" method="POST" class="delete-form">
                                            <input type="hidden" name="id" value="<?= $order['id'] ?>">
                                            <button type="submit" class="button button--small button--danger"
                                                    onclick="return confirm('この注文を削除してもよろしいですか？');">
                                                <i class="fas fa-trash"></i>
                                                削除
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.search-form {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.search-form__input {
    padding: 0.5rem 1rem;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    min-width: 300px;
}

.button-group {
    display: flex;
    gap: 0.5rem;
}

.delete-form {
    margin: 0;
}
</style> 