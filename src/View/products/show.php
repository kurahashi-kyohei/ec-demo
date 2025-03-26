<?php require __DIR__ . '/../layout/header.php'; ?>

<main class="product-detail">
    <div class="product-detail__container">
        <div class="product-detail__image">
            <img src="<?= htmlspecialchars($product['image_path'] ?? '/assets/images/no-image.jpg') ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>">
        </div>

        <div class="product-detail__content">
            <h1 class="product-detail__name"><?= htmlspecialchars($product['name']) ?></h1>
            <?php if (isset($product['category'])): ?>
                <p class="product-detail__category"><?= htmlspecialchars($product['category']) ?></p>
            <?php endif; ?>
            <p class="product-detail__price">¥<?= number_format($product['price']) ?></p>
            
            <div class="product-detail__description">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </div>

            <div class="product-detail__stock">
                <?php if ($product['stock'] > 0): ?>
                    <p class="product-detail__stock-status product-detail__stock-status--available">
                        在庫あり（残り<?= $product['stock'] ?>個）
                    </p>
                <?php else: ?>
                    <p class="product-detail__stock-status product-detail__stock-status--unavailable">
                        在庫切れ
                    </p>
                <?php endif; ?>
            </div>

            <div class="product-detail__actions">
                <div class="product-detail__buttons">
                    <?php if ($product['stock'] > 0): ?>
                        <form action="/cart/add" method="post" class="add-to-cart-form">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <div class="quantity-input">
                                <label for="quantity">数量:</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                            </div>
                            <button type="submit" class="button button--primary">
                                <i class="fas fa-shopping-cart"></i>
                                カートに追加
                            </button>
                        </form>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="button <?= $isFavorite ? 'button--favorite-active' : 'button--favorite' ?>"
                                data-product-id="<?= $product['id'] ?>"
                                onclick="toggleFavorite(this)">
                            <i class="fas fa-heart"></i>
                            <?= $isFavorite ? 'お気に入り済み' : 'お気に入りに追加' ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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