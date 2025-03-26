<?php require __DIR__ . '/../../layout/admin/header.php'; ?>

<div class="admin-products">
    <div class="admin-products__container">
        <div class="admin-products__header">
            <h1 class="admin-products__title">商品管理</h1>
            <a href="/admin/products/create" class="button button--primary">
                <i class="fas fa-plus"></i>
                新規商品登録
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <div class="admin-products__empty">
                <p>商品が登録されていません。</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>画像</th>
                            <th>商品名</th>
                            <th>価格</th>
                            <th>在庫数</th>
                            <th>登録日</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= $product['id'] ?></td>
                                <td>
                                    <?php if ($product['image_path']): ?>
                                        <img src="<?= htmlspecialchars($product['image_path']) ?>" 
                                        alt="<?= htmlspecialchars($product['name']) ?>"
                                        class="product-thumbnail">
                                    <?php else: ?>
                                        <span class="no-image">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td>¥<?= number_format($product['price']) ?></td>
                                <td>
                                    <span class="stock-badge <?= $product['stock'] < 5 ? 'stock-badge--low' : '' ?>">
                                        <?= $product['stock'] ?>
                                    </span>
                                </td>
                                <td><?= date('Y/m/d', strtotime($product['created_at'])) ?></td>
                                <td class="admin-table__actions">
                                    <a href="/admin/products/edit/<?= $product['id'] ?>" 
                                       class="button button--small button--secondary">
                                        <i class="fas fa-edit"></i>
                                        編集
                                    </a>
                                    <form action="/admin/products/delete/<?= $product['id'] ?>" 
                                          method="post"
                                          class="delete-form"
                                          onsubmit="return confirm('本当に削除しますか？');">
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
