<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="auth">
    <div class="auth__container">
        <h1 class="auth__title">ログイン</h1>

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

        <form action="/login" method="POST" class="auth__form">
            <div class="form-group">
                <label for="email" class="form-group__label">メールアドレス</label>
                <input type="email" id="email" name="email" class="form-group__input" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-group__label">パスワード</label>
                <input type="password" id="password" name="password" class="form-group__input" required>
            </div>

            <div class="auth__links">
                <a href="/forgot-password" class="auth__forgot-link">パスワードをお忘れの方</a>
            </div>

            <button type="submit" class="auth__submit">ログイン</button>

            <div class="auth__register">
                <p>アカウントをお持ちでない方は<a href="/register">新規登録</a></p>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?> 