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

    <div class="admin-users__container">
        <form action="/admin/users/update/<?= $user['id'] ?>" method="post" class="user-form">
            <?php if (isset($error)): ?>
                <div class="alert alert--error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="user-form__group">
                <label for="email" class="user-form__label">メールアドレス</label>
                <input type="email" id="email" name="email" class="user-form__input" 
                       value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="user-form__group">
                <label for="password" class="user-form__label">
                    パスワード
                    <span class="user-form__label-note">（変更する場合のみ入力）</span>
                </label>
                <input type="password" id="password" name="password" class="user-form__input">
            </div>

            <div class="user-form__row">
                <div class="user-form__group">
                    <label for="last_name" class="user-form__label">姓</label>
                    <input type="text" id="last_name" name="last_name" class="user-form__input" 
                           value="<?= htmlspecialchars($user['last_name']) ?>" required>
                </div>

                <div class="user-form__group">
                    <label for="first_name" class="user-form__label">名</label>
                    <input type="text" id="first_name" name="first_name" class="user-form__input" 
                           value="<?= htmlspecialchars($user['first_name']) ?>" required>
                </div>
            </div>

            <div class="user-form__group">
                <label for="role" class="user-form__label">権限</label>
                <select id="role" name="role" class="user-form__input" required 
                        <?= $user['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>
                        一般ユーザー
                    </option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>
                        管理者
                    </option>
                </select>
                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                    <input type="hidden" name="role" value="<?= $user['role'] ?>">
                <?php endif; ?>
            </div>

            <div class="user-form__group">
                <label for="status" class="user-form__label">ステータス</label>
                <select id="status" name="status" class="user-form__input" required
                        <?= $user['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                    <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>
                        有効
                    </option>
                    <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>
                        無効
                    </option>
                </select>
                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                    <input type="hidden" name="status" value="<?= $user['status'] ?>">
                <?php endif; ?>
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