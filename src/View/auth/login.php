<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- reCAPTCHA v3 APIの読み込み -->
<script src="https://www.google.com/recaptcha/api.js?render=<?= htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY']) ?>"></script>

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

        <form action="/login" method="POST" class="auth__form" id="login-form">
            <div class="form-group">
                <label for="email" class="form-group__label">メールアドレス</label>
                <input type="email" id="email" name="email" class="form-group__input" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password" class="form-group__label">パスワード</label>
                <input type="password" id="password" name="password" class="form-group__input" required autocomplete="current-password">
            </div>

            <div class="auth__links">
                <a href="/forgot-password" class="auth__forgot-link">パスワードをお忘れの方</a>
            </div>

            <input type="hidden" name="recaptcha_response" id="recaptchaResponse">

            <button type="submit" class="auth__submit">ログイン</button>

            <div class="auth__register">
                <p>アカウントをお持ちでない方は<a href="/register">新規登録</a></p>
            </div>
        </form>

        <div class="auth__social">
            <div class="auth__divider">
                <span>または</span>
            </div>
            <a href="/auth/google" class="auth__social-button auth__social-button--google">
                <img src="/img/google-icon.svg" alt="Google" class="auth__social-icon">
                Googleアカウントでログイン
            </a>
        </div>
    </div>
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const googleButton = document.querySelector('.auth__social-button--google');
    if (googleButton) {
        googleButton.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            window.location.href = href;
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?> 