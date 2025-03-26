<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="mypage">
    <h1 class="mypage__title">パスワード変更</h1>

    <div class="mypage__section">
        <form action="/mypage/password" method="POST" class="form">
            <div class="form-group">
                <label for="current_password">現在のパスワード</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>

            <div class="form-group">
                <label for="new_password">新しいパスワード</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>

            <div class="form-group">
                <label for="new_password_confirmation">新しいパスワード（確認）</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation" required>
            </div>

            <div class="mypage__buttons">
                <button type="submit" class="mypage__button">変更する</button>
                <a href="/mypage" class="mypage__button mypage__button--danger">キャンセル</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?> 