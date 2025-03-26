<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="mypage">
    <div class="mypage__container">
        <h1 class="mypage__title">プロフィール編集</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert--error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="/mypage/update-profile" method="POST" class="form">
            <div class="form-group">
                <label for="first_name" class="form-group__label">名</label>
                <input type="text" id="first_name" name="first_name" 
                       class="form-group__input" 
                       value="<?= htmlspecialchars($user['first_name']) ?>" 
                       required>
            </div>

            <div class="form-group">
                <label for="last_name" class="form-group__label">姓</label>
                <input type="text" id="last_name" name="last_name" 
                       class="form-group__input" 
                       value="<?= htmlspecialchars($user['last_name']) ?>" 
                       required>
            </div>

            <div class="form-group">
                <label class="form-group__label">メールアドレス</label>
                <p class="form-group__text"><?= htmlspecialchars($user['email']) ?></p>
                <small class="form-group__help">メールアドレスの変更は現在サポートしていません</small>
            </div>

            <div class="form__actions">
                <button type="submit" class="button">更新する</button>
                <a href="/mypage" class="button button--secondary">キャンセル</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?> 