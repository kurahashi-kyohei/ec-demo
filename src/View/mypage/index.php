<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="mypage">
    <h1 class="mypage__title">マイページ</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert--success">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div class="mypage__section">
        <h2 class="mypage__section-title">プロフィール情報</h2>
        <dl class="mypage__info">
            <dt>氏名</dt>
            <dd><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></dd>
            
            <dt>メールアドレス</dt>
            <dd><?= htmlspecialchars($user['email']) ?></dd>
        </dl>

        <div class="mypage__menu">
            <a href="/mypage/edit" class="mypage__menu-item">
                <i class="fas fa-user-edit"></i>
                プロフィール編集
            </a>
            <a href="/mypage/card/register" class="mypage__menu-item">
                <i class="fas fa-credit-card"></i>
                クレジットカード登録/編集
            </a>
            <a href="/mypage/password" class="mypage__menu-item">
                <i class="fas fa-key"></i>
                パスワード変更
            </a>
            <a href="/favorites" class="mypage__menu-item">
                <i class="fas fa-heart"></i>
                お気に入り商品
            </a>
            <form action="/logout" method="POST" class="mypage__logout">
                <button type="submit" class="logout-btn">ログアウト</button>
            </form>
        </div>
    </div>

    <div class="mypage__section">
        <h2 class="mypage__section-title">購入履歴</h2>
        <?php if (empty($orders)): ?>
            <p>購入履歴はありません。</p>
        <?php else: ?>
            <!-- 購入履歴の表示 -->
        <?php endif; ?>
    </div>

    <form action="/mypage/deactivate" method="POST" class="mypage__danger-zone" 
            onsubmit="return confirm('本当に退会してもよろしいですか？\nこの操作は取り消すことができません。');">
        <button type="submit" class="button button--danger">
            <i class="fas fa-user-times"></i>
            退会する
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?> 