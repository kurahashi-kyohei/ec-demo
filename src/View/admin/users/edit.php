<?php require __DIR__ . '/../../layout/admin/header.php'; ?>

<div class="admin-users">
    <div class="admin-users__header">
        <div class="admin-users__header-inner">
            <h1 class="admin-users__title">
                <i class="fas fa-user-edit"></i>
                ユーザー編集
            </h1>
            <a href="/admin/users" class="button button--secondary">
                <i class="fas fa-arrow-left"></i>
                ユーザー一覧に戻る
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert--error">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="/admin/users/update/<?= $user['id'] ?>" method="post" class="user-form">
        <div class="form-group">
            <label for="email" class="form-group__label">メールアドレス</label>
            <input type="email" id="email" name="email" class="form-group__input" 
                   value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="form-group">
            <label for="last_name" class="form-group__label">姓</label>
            <input type="text" id="last_name" name="last_name" class="form-group__input" 
                   value="<?= htmlspecialchars($user['last_name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="first_name" class="form-group__label">名</label>
            <input type="text" id="first_name" name="first_name" class="form-group__input" 
                   value="<?= htmlspecialchars($user['first_name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="phone_number" class="form-group__label">電話番号（必須）</label>
            <input type="tel" id="phone_number" name="phone_number" class="form-group__input" 
                   value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>" 
                   pattern="[0-9-]{10,}"
                   title="正しい電話番号の形式で入力してください"
                   required>
        </div>

        <div class="form-group">
            <label for="address" class="form-group__label">住所（任意）</label>
            <textarea id="address" name="address" class="form-group__input"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="status" class="form-group__label">ステータス</label>
            <select id="status" name="status" class="form-group__input">
                <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>有効</option>
                <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>無効</option>
            </select>
        </div>

        <div class="form-group">
            <label for="role" class="form-group__label">権限</label>
            <select id="role" name="role" class="form-group__input">
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>一般ユーザー</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>管理者</option>
            </select>
        </div>

            <div class="user-form__actions">
                <button type="submit" class="button button--primary">
                    <i class="fas fa-save"></i>
                    変更を保存
                </button>
                <a href="/admin/users" class="button button--secondary">
                    <i class="fas fa-times"></i>
                    キャンセル
                </a>
            </div>
        </form>
    </div>
</div>