<?php require __DIR__ . '/../../layout/admin/header.php'; ?>

<div class="admin-products">
    <div class="admin-products__header">
        <div class="admin-products__header-inner">
            <h1 class="admin-products__title">
                <i class="fas fa-plus"></i>
                商品登録
            </h1>
            <a href="/admin/products" class="button button--secondary">
                <i class="fas fa-arrow-left"></i>
                商品一覧に戻る
            </a>
        </div>
    </div>

    <div class="admin-products__container">
        <form action="/admin/products/store" method="post" enctype="multipart/form-data" class="product-form">
            <?php if (isset($error)): ?>
                <div class="alert alert--error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="product-form__group">
                <label for="name" class="product-form__label">商品名</label>
                <input type="text" id="name" name="name" class="product-form__input" required>
            </div>

            <div class="product-form__group">
                <label for="category" class="product-form__label">カテゴリー</label>
                <select id="category" name="category" class="product-form__input" required>
                    <option value="">カテゴリーを選択</option>
                    <option value="ミノー">ミノー</option>
                    <option value="スプーン">スプーン</option>
                    <option value="スピナー">スピナー</option>
                    <option value="others">その他</option>
                </select>
            </div>

            <div class="product-form__group">
                <label for="description" class="product-form__label">商品説明</label>
                <textarea id="description" name="description" class="product-form__input product-form__textarea" required></textarea>
            </div>

            <div class="product-form__group">
                <label for="price" class="product-form__label">価格</label>
                <input type="number" id="price" name="price" class="product-form__input" min="0" required>
            </div>

            <div class="product-form__group">
                <label for="stock" class="product-form__label">在庫数</label>
                <input type="number" id="stock" name="stock" class="product-form__input" min="0" required>
            </div>

            <div class="product-form__group">
                <label for="image" class="product-form__label">商品画像</label>
                <input type="file" id="image" name="image" class="product-form__input" accept="image/*">
            </div>

            <div class="product-form__actions">
                <button type="submit" class="button button--primary">
                    <i class="fas fa-save"></i>
                    登録する
                </button>
                <a href="/admin/products" class="button button--secondary">
                    <i class="fas fa-times"></i>
                    キャンセル
                </a>
            </div>
        </form>
    </div>
</div>
