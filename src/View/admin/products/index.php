<?php require_once __DIR__ . '/../../layout/admin/header.php'; ?>

<div class="admin-products">
    <div class="admin-products__container">
        <div class="admin-products__header">
            <h1 class="admin-products__title">商品管理</h1>
            <div>
                <button type="button" class="button button--secondary">
                    <a href="/admin/products/export-csv">CSVエクスポート</a>
                </button>
                <a href="/admin/products/create" class="button button--primary">
                    <i class="fas fa-plus"></i>
                    新規商品登録
                </a>
            </div>
        </div>

        <?php if (empty($products)): ?>
            <div class="admin-products__empty">
                <p>商品が登録されていません。</p>
            </div>
        <?php else: ?>
            <div class="table">
                <form action="/admin/products" method="get" class="products__search admin-search">
                    <div class="products__search-input">
                        <input type="text" 
                            name="keyword" 
                            value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" 
                            placeholder="商品名を入力"
                            class="search-input">
                        <button type="submit" class="search-button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <div class="products__search-category">
                        <select name="category" class="category-select">
                            <option value="">すべてのカテゴリー</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" 
                                        <?= ($cat === ($_GET['category'] ?? '')) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="button search-button">検索する</button>
                </form>
                <form id="bulkActionForm" action="/admin/products/bulk-delete" method="post" onsubmit="return confirmBulkDelete();">
                    <button type="submit" class="btn-bulk-delete" id="bulkDeleteBtn" disabled>
                        <i class="fas fa-trash"></i>
                        一括削除する
                    </button>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width: 40px">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                    </div>
                                </th>
                                <th style="width: 80px">
                                    <?php
                                    $idOrder = ($currentSort === 'id' && $currentOrder === 'asc') ? 'desc' : 'asc';
                                    $idIcon = ($currentSort === 'id') ? ($currentOrder === 'asc' ? '↑' : '↓') : '↕';
                                    ?>
                                    <a href="?sort=id&order=<?= $idOrder ?><?= isset($_GET['keyword']) ? '&keyword=' . htmlspecialchars($_GET['keyword']) : '' ?><?= isset($_GET['category']) ? '&category=' . htmlspecialchars($_GET['category']) : '' ?>" class="text-dark">
                                        ID <?= $idIcon ?>
                                    </a>
                                </th>
                                <th style="width: 120px">画像</th>
                                <th>
                                    <?php
                                    $nameOrder = ($currentSort === 'name' && $currentOrder === 'asc') ? 'desc' : 'asc';
                                    $nameIcon = ($currentSort === 'name') ? ($currentOrder === 'asc' ? '↑' : '↓') : '↕';
                                    ?>
                                    <a href="?sort=name&order=<?= $nameOrder ?><?= isset($_GET['keyword']) ? '&keyword=' . htmlspecialchars($_GET['keyword']) : '' ?><?= isset($_GET['category']) ? '&category=' . htmlspecialchars($_GET['category']) : '' ?>" class="text-dark">
                                        商品名 <?= $nameIcon ?>
                                    </a>
                                </th>
                                <th style="width: 120px">
                                    <?php
                                    $priceOrder = ($currentSort === 'price' && $currentOrder === 'asc') ? 'desc' : 'asc';
                                    $priceIcon = ($currentSort === 'price') ? ($currentOrder === 'asc' ? '↑' : '↓') : '↕';
                                    ?>
                                    <a href="?sort=price&order=<?= $priceOrder ?><?= isset($_GET['keyword']) ? '&keyword=' . htmlspecialchars($_GET['keyword']) : '' ?><?= isset($_GET['category']) ? '&category=' . htmlspecialchars($_GET['category']) : '' ?>" class="text-dark">
                                        価格 <?= $priceIcon ?>
                                    </a>
                                </th>
                                <th style="width: 100px">
                                    <?php
                                    $stockOrder = ($currentSort === 'stock' && $currentOrder === 'asc') ? 'desc' : 'asc';
                                    $stockIcon = ($currentSort === 'stock') ? ($currentOrder === 'asc' ? '↑' : '↓') : '↕';
                                    ?>
                                    <a href="?sort=stock&order=<?= $stockOrder ?><?= isset($_GET['keyword']) ? '&keyword=' . htmlspecialchars($_GET['keyword']) : '' ?><?= isset($_GET['category']) ? '&category=' . htmlspecialchars($_GET['category']) : '' ?>" class="text-dark">
                                        在庫数 <?= $stockIcon ?>
                                    </a>
                                </th>
                                <th style="width: 120px">
                                    <?php
                                    $dateOrder = ($currentSort === 'created_at' && $currentOrder === 'asc') ? 'desc' : 'asc';
                                    $dateIcon = ($currentSort === 'created_at') ? ($currentOrder === 'asc' ? '↑' : '↓') : '↕';
                                    ?>
                                    <a href="?sort=created_at&order=<?= $dateOrder ?><?= isset($_GET['keyword']) ? '&keyword=' . htmlspecialchars($_GET['keyword']) : '' ?><?= isset($_GET['category']) ? '&category=' . htmlspecialchars($_GET['category']) : '' ?>" class="text-dark">
                                        登録日 <?= $dateIcon ?>
                                    </a>
                                </th>
                                <th style="width: 160px">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr class="product-thumbnail">
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input product-checkbox" type="checkbox" 
                                                   name="product_ids[]" value="<?php echo $product['id']; ?>">
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                                    <td>
                                        <?php if ($product['image_path']): ?>
                                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 class="img-thumbnail" 
                                                 style="max-width: 80px; max-height: 80px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 80px; height: 80px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($product['name']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($product['category']); ?></div>
                                    </td>
                                    <td>¥<?php echo number_format((int)$product['price']); ?></td>
                                    <td>
                                        <span class="stock-badge <?= $product['stock'] < 5 ? 'stock-badge--low' : '' ?>">
                                            <?= $product['stock'] ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y/m/d', strtotime($product['created_at'])); ?></td>
                                    <td class="table__actions">
                                        <a href="/admin/products/edit/<?php echo $product['id']; ?>" 
                                           class="button button--small button--secondary">
                                            <i class="fas fa-edit"></i>
                                            編集
                                        </a>
                                        <form action="/admin/products/delete/<?php echo $product['id']; ?>" 
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
                </form>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <span class="table__count">
                            全<?php echo number_format($totalProducts); ?>件中
                            <?php
                            $start = ($currentPage - 1) * $perPage + 1;
                            $end = min($currentPage * $perPage, $totalProducts);
                            echo number_format($start) . '～' . number_format($end);
                            ?>件を表示
                        </span>
                        <div class="pagination__inner">
                            <?php if ($currentPage > 1): ?>
                                <a href="?page=1" class="pagination__item">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                                <a href="?page=<?php echo $currentPage - 1; ?>" class="pagination__item">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $currentPage - 2);
                            $end = min($totalPages, $currentPage + 2);
                            
                            if ($start > 1) {
                                echo '<span class="pagination__item pagination__item--dots">...</span>';
                            }
                            
                            for ($i = $start; $i <= $end; $i++) {
                                if ($i == $currentPage) {
                                    echo '<span class="pagination__item pagination__item--current">' . $i . '</span>';
                                } else {
                                    echo '<a href="?page=' . $i . '" class="pagination__item">' . $i . '</a>';
                                }
                            }
                            
                            if ($end < $totalPages) {
                                echo '<span class="pagination__item pagination__item--dots">...</span>';
                            }
                            ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <a href="?page=<?php echo $currentPage + 1; ?>" class="pagination__item">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                                <a href="?page=<?php echo $totalPages; ?>" class="pagination__item">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkActionForm = document.getElementById('bulkActionForm');

    selectAllCheckbox.addEventListener('change', function() {
        productCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkDeleteButton();
    });

    productCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkDeleteButton();
 
            selectAllCheckbox.checked = [...productCheckboxes].every(cb => cb.checked);
            selectAllCheckbox.indeterminate = !selectAllCheckbox.checked && 
                                            [...productCheckboxes].some(cb => cb.checked);
        });
    });

    function updateBulkDeleteButton() {
        const checkedCount = [...productCheckboxes].filter(cb => cb.checked).length;
        bulkDeleteBtn.disabled = checkedCount === 0;
    }

    bulkActionForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;
        if (checkedCount === 0) {
            alert('削除する商品を選択してください。');
            return false;
        }
        if (confirm(`選択した${checkedCount}件の商品を削除してもよろしいですか？`)) {
            this.submit();
        }
    });
});
</script>
