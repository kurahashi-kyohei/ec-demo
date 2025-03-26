<?php require __DIR__ . '/../../layout/admin/header.php'; ?>

<div class="admin-users">
    <div class="admin-users__header">
        <div class="admin-users__header-inner">
            <h1 class="admin-users__title">
                ユーザー管理
            </h1>
            <a href="/admin/users/create" class="button button--primary">
                <i class="fas fa-user-plus"></i>
                新規ユーザー登録
            </a>
        </div>
    </div>

    <div class="admin-users__container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert--success">
                <i class="fas fa-check-circle"></i>
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert--error">
                <i class="fas fa-exclamation-circle"></i>
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (empty($users)): ?>
            <div class="admin-users__empty">
                <p>ユーザーが登録されていません</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>名前</th>
                            <th>メールアドレス</th>
                            <th>権限</th>
                            <th>ステータス</th>
                            <th>登録日</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-badge--<?php echo $user['role']; ?>">
                                        <?php echo $user['role'] === 'admin' ? '管理者' : '一般ユーザー'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-badge--<?php echo $user['status']; ?>">
                                        <?php echo $user['status'] === 'active' ? '有効' : '無効'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y/m/d', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/admin/users/edit/<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                            編集
                                        </a>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <form action="/admin/users/delete/<?php echo $user['id']; ?>" 
                                                  method="POST" 
                                                  class="delete-form"
                                                  onsubmit="return confirm('このユーザーを削除してもよろしいですか？');">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                    削除
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>