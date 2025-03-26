<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="auth">
    <div class="auth__container">
        <h1 class="auth__title">新規登録</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert--error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="/register" method="POST" class="auth__form">
            <div class="form-group">
                <label for="email" class="form-group__label">メールアドレス</label>
                <input type="email" id="email" name="email" class="form-group__input" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-group__label">パスワード</label>
                <input type="password" id="password" name="password" class="form-group__input" 
                       required minlength="8" 
                       pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$"
                       title="パスワードは8文字以上で、少なくとも1つの文字と1つの数字を含む必要があります">
                <small class="form-group__help">8文字以上で、文字と数字を含めてください</small>
            </div>

            <div class="form-group">
                <label for="first_name" class="form-group__label">名</label>
                <input type="text" id="first_name" name="first_name" class="form-group__input" required>
            </div>

            <div class="form-group">
                <label for="last_name" class="form-group__label">姓</label>
                <input type="text" id="last_name" name="last_name" class="form-group__input" required>
            </div>

            <button type="submit" class="auth__submit">登録する</button>

            <div class="auth__login">
                <p>既にアカウントをお持ちの方は<a href="/login">ログイン</a></p>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?> 