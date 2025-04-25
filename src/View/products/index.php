<?php require __DIR__ . '/../layout/header.php'; ?>

<main class="products">
    <div class="products__container">
        <div class="products__header">
            <h1 class="products__title">商品一覧</h1>
            
            <form action="/products" method="get" class="products__search">
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
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['id']) ?>" 
                                    <?= ($category['id'] === ($_GET['category'] ?? '')) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="button search-button">検索する</button>

                <div class="products__search-sort">
                    <select name="sort" class="sort-select">
                        <option value="id">新着順</option>
                        <option value="price">価格順</option>
                    </select>
                </div>
            </form>
        </div>

        <?php if (empty($products)): ?>
            <div class="products__empty">
                <p>商品が見つかりませんでした。</p>
                <?php if (isset($_GET['keyword']) || isset($_GET['category'])): ?>
                    <a href="/products" class="button button--secondary">すべての商品を表示</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="products__grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <a href="/products/<?= $product['id'] ?>" class="product-card__link">
                            <div class="product-card__image">
                                <img src="<?= htmlspecialchars($product['image_path']) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>">
                            </div>
                            <div class="product-card__content">
                                <h2 class="product-card__name"><?= htmlspecialchars($product['name']) ?></h2>
                                <p class="product-card__price">¥<?= number_format($product['price']) ?></p>
                                <?php if ($product['stock'] > 0): ?>
                                    <p class="product-card__stock product-card__stock--available">在庫あり</p>
                                <?php else: ?>
                                    <p class="product-card__stock product-card__stock--unavailable">在庫切れ</p>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

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
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>