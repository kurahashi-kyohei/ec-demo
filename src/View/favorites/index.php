<?php require __DIR__ . '/../layout/header.php'; ?>

<main class="favorites">
    <div class="favorites__container">
        <h1 class="favorites__title">お気に入り商品</h1>

        <?php if (empty($favorites)): ?>
            <div class="favorites__empty">
                <p>お気に入りに登録された商品はありません。</p>
                <a href="/products" class="button">商品一覧へ戻る</a>
            </div>
        <?php else: ?>
            <div class="favorites__grid">
                <?php foreach ($favorites as $product): ?>
                    <div class="product-card">
                        <div class="product-card__image">
                            <img src="<?= htmlspecialchars($product['image_path']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                        </div>
                        <div class="product-card__content">
                            <h2 class="product-card__name">
                                <a href="/products/<?= $product['id'] ?>">
                                    <?= htmlspecialchars($product['name']) ?>
                                </a>
                            </h2>
                            <p class="product-card__price">¥<?= number_format($product['price']) ?></p>
                            <div class="product-card__actions">
                                <button class="button button--favorite-active"
                                        data-product-id="<?= $product['id'] ?>"
                                        onclick="toggleFavorite(this)">
                                    <i class="fas fa-heart"></i>
                                    お気に入り済み
                                </button>
                                <a href="/products/<?= $product['id'] ?>" class="button">
                                    詳細を見る
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
async function toggleFavorite(button) {
    const productId = button.dataset.productId;
    const isActive = button.classList.contains('button--favorite-active');
    const url = isActive ? '/favorites/remove' : '/favorites/add';

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}`
        });

        const data = await response.json();
        
        if (data.success) {
            button.classList.toggle('button--favorite-active');
            button.classList.toggle('button--favorite');
            button.innerHTML = `<i class="fas fa-heart"></i> ${isActive ? 'お気に入りに追加' : 'お気に入り済み'}`;
        } else {
            alert(data.error || 'エラーが発生しました。');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('エラーが発生しました。');
    }
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?> 