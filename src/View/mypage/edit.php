<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="mypage">
    <h1 class="mypage__title">プロフィール編集</h1>

    <div class="mypage__section">
        <form action="/mypage/update" method="POST" class="form">
            <div class="form-group">
                <label for="first_name">姓</label>
                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="last_name">名</label>
                <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="mypage__buttons">
                <button type="submit" class="mypage__button">更新する</button>
                <a href="/mypage" class="mypage__button mypage__button--danger">キャンセル</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?> 