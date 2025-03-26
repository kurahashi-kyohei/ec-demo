<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="mypage">
    <div class="mypage__container">
        <h1 class="mypage__title">パスワード変更</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert--error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="/mypage/update-password" method="POST" class="form">
            <div class="form-group">
                <label for="current_password" class="form-group__label">現在のパスワード</label>
                <input type="password" id="current_password" name="current_password" 
                       class="form-group__input" required>
            </div>

            <div class="form-group">
                <label for="new_password" class="form-group__label">新しいパスワード</label>
                <input type="password" id="new_password" name="new_password" 
                       class="form-group__input" required>
                <small class="form-group__help">8文字以上で入力してください</small>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="form-group__label">新しいパスワード（確認）</label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       class="form-group__input" required>
            </div>

            <div class="form__actions">
                <button type="submit" class="button">変更する</button>
                <a href="/mypage" class="button button--secondary">キャンセル</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?> 