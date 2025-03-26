<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="error-page">
    <div class="error-page__container">
        <div class="error-page__content">
            <h1 class="error-page__title">
                <i class="fas fa-exclamation-circle"></i>
                404 Not Found
            </h1>
            <p class="error-page__message">
                申し訳ありませんが、お探しのページが見つかりませんでした。
            </p>
            <div class="error-page__actions">
                <a href="/" class="button button--primary">
                    <i class="fas fa-home"></i>
                    ホームに戻る
                </a>
                <button onclick="history.back()" class="button button--secondary">
                    <i class="fas fa-arrow-left"></i>
                    前のページに戻る
                </button>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?> 