<?php require __DIR__ . '/../../layout/admin/header.php'; ?>

<div class="admin-users">
    <div class="admin-users__header">
        <div class="admin-users__header-inner">
            <h1 class="admin-users__title">
                <i class="fas fa-user-plus"></i>
                ユーザー登録
            </h1>
            <a href="/admin/users" class="button button--secondary">
                <i class="fas fa-arrow-left"></i>
                ユーザー一覧に戻る
            </a>
        </div>
    </div>

    <div class="admin-users__container">
        <form action="/admin/users/store" method="post" class="user-form">
            <?php if (isset($error)): ?>
                <div class="alert alert--error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="user-form__group">
                <label for="email" class="user-form__label">メールアドレス</label>
                <input type="email" id="email" name="email" class="user-form__input" required>
            </div>

            <div class="user-form__group">
                <label for="phone_number" class="user-form__label">電話番号</label>
                <input type="phone_number" id="phone_number" name="phone_number" class="user-form__input" required>
            </div>

            <div class="user-form__group">
                <label for="password" class="user-form__label">パスワード</label>
                <input type="password" id="password" name="password" class="user-form__input" required>
            </div>

            <div class="user-form__row">
                <div class="user-form__group">
                    <label for="last_name" class="user-form__label">姓</label>
                    <input type="text" id="last_name" name="last_name" class="user-form__input" required>
                </div>

                <div class="user-form__group">
                    <label for="first_name" class="user-form__label">名</label>
                    <input type="text" id="first_name" name="first_name" class="user-form__input" required>
                </div>
            </div>

            <div class="user-form__group">
                <label for="role" class="user-form__label">権限</label>
                <select id="role" name="role" class="user-form__input" required>
                    <option value="user">一般ユーザー</option>
                    <option value="admin">管理者</option>
                </select>
            </div>

            <div class="user-form__group">
                <label for="status" class="user-form__label">ステータス</label>
                <select id="status" name="status" class="user-form__input" required>
                    <option value="active">有効</option>
                    <option value="inactive">無効</option>
                </select>
            </div>

            <div class="user-form__actions">
                <button type="submit" class="button button--primary">
                    <i class="fas fa-save"></i>
                    登録する
                </button>
                <a href="/admin/users" class="button button--secondary">
                    <i class="fas fa-times"></i>
                    キャンセル
                </a>
            </div>
        </form>
    </div>
</div>
