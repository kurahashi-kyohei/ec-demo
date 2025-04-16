<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- reCAPTCHA v3 APIの読み込み -->
<script src="https://www.google.com/recaptcha/api.js?render=<?= htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY']) ?>"></script>

<div class="auth">
    <div class="auth__container">
        <h1 class="auth__title">新規登録</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert--error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="/register" method="POST" class="auth__form" id="register-form">
            <div class="form-group">
                <label for="email" class="form-group__label">メールアドレス</label>
                <input type="email" id="email" name="email" class="form-group__input" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password" class="form-group__label">パスワード</label>
                <input type="password" id="password" name="password" class="form-group__input" required autocomplete="new-password">
                <small class="form-group__help">8文字以上で、文字と数字を含める必要があります</small>
            </div>

            <div class="form-group">
                <label for="last_name" class="form-group__label">姓</label>
                <input type="text" id="last_name" name="last_name" class="form-group__input" required autocomplete="family-name">
            </div>

            <div class="form-group">
                <label for="first_name" class="form-group__label">名</label>
                <input type="text" id="first_name" name="first_name" class="form-group__input" required autocomplete="given-name">
            </div>

            <div class="form-group">
                <label for="phone_number" class="form-group__label">電話番号</label>
                <input type="tel" id="phone_number" name="phone_number" class="form-group__input" required autocomplete="tel">
            </div>

            <div class="form-group">
                <label for="address" class="form-group__label">住所</label>
                <input type="text" id="address" name="address" class="form-group__input" required autocomplete="street-address">
            </div>

            <!-- reCAPTCHA v3のトークンを格納する隠しフィールド -->
            <input type="hidden" name="recaptcha_response" id="recaptchaResponse">

            <button type="submit" class="auth__submit">登録</button>

            <div class="auth__login">
                <p>既にアカウントをお持ちの方は<a href="/login">ログイン</a></p>
            </div>
        </form>
    </div>
</div>

<script>
// フォームの送信をハンドリング
const form = document.getElementById('register-form');
form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // reCAPTCHAトークンを取得
    grecaptcha.ready(function() {
        grecaptcha.execute('<?= htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY']) ?>', {action: 'register'})
        .then(function(token) {
            // トークンを設定
            document.getElementById('recaptchaResponse').value = token;
            // 通常のフォーム送信を実行
            form.submit();
        })
        .catch(function(error) {
            console.error('reCAPTCHA Error:', error);
            alert('認証処理中にエラーが発生しました。もう一度お試しください。');
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?> 