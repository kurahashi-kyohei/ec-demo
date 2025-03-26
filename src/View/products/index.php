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
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>