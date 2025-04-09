<?php require __DIR__ . '/../layout/header.php'; ?>

<main class="cart">
    <div class="cart__container">
        <h1 class="cart__title">ショッピングカート</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert--error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert--success">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="cart__empty">
                <p>カートに商品がありません。</p>
                <a href="/products" class="button">商品一覧へ戻る</a>
            </div>
        <?php else: ?>
            <div class="cart__items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item__image">
                            <img src="<?= htmlspecialchars($item['product']['image_path']) ?>" 
                                 alt="<?= htmlspecialchars($item['product']['name']) ?>">
                        </div>
                        <div class="cart-item__content">
                            <h2 class="cart-item__name">
                                <a href="/products/<?= $item['product']['id'] ?>">
                                    <?= htmlspecialchars($item['product']['name']) ?>
                                </a>
                            </h2>
                            <p class="cart-item__price">¥<?= number_format($item['product']['price']) ?></p>
                            
                            <form action="/cart/update" method="POST" class="cart-item__quantity-form">
                                <input type="hidden" name="product_id" value="<?= $item['product']['id'] ?>">
                                <select name="quantity" onchange="this.form.submit()">
                                    <?php for ($i = 1; $i <= min(10, $item['product']['stock']); $i++): ?>
                                        <option value="<?= $i ?>" <?= $item['quantity'] === $i ? 'selected' : '' ?>>
                                            <?= $i ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </form>
                            
                            <p class="cart-item__subtotal">
                                小計: ¥<?= number_format($item['subtotal']) ?>
                            </p>

                            <form action="/cart/remove" method="POST" class="cart-item__remove-form">
                                <input type="hidden" name="product_id" value="<?= $item['product']['id'] ?>">
                                <button type="submit" class="cart-item__remove-button">削除</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart__summary">
                <div class="cart__total">
                    <span class="cart__total-label">合計</span>
                    <span class="cart__total-amount">¥<?= number_format($total) ?></span>
                </div>

                <div class="cart__actions">
                    <form action="/cart/clear" method="POST" class="cart__clear-form">
                        <button type="submit" class="button button--secondary">カートを空にする</button>
                    </form>
                    <form action="/cart/order" method="POST">
                        <button type="submit" class="button">注文を確定する</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?> 