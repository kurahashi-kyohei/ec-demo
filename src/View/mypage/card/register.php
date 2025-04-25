<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<div class="card-registration">
    <h1 class="card-registration__title">クレジットカード登録/編集</h1>

    <div class="card-registration__section">
        <form action="/mypage/card/store" method="POST" class="form" id="cardForm">
            <div class="form-group">
                <label for="card_number">カード番号</label>
                <input type="text" id="card_number" name="card_number" 
                    value="<?php echo $user['card_number'] ?? ''; ?>"
                    pattern="\d{4}-\d{4}-\d{4}-\d{4}" 
                    placeholder="1234-5678-9012-3456" 
                    required>
            </div>

            <div class="form-group">
                <label for="card_holder">カード名義人</label>
                <input type="text" id="card_holder" name="card_holder" 
                    value="<?php echo $user['card_holder'] ?? ''; ?>"
                    pattern="^[a-zA-Z\s]+$"
                    placeholder="TARO YAMADA" 
                    required>
                <small class="form-text">ローマ字で入力してください</small>
            </div>

            <div class="form-row">
                <div class="form-group form-group--half">
                    <label for="card_expiry">有効期限</label>
                    <input type="text" id="card_expiry" name="card_expiry" 
                        value="<?php echo $user['card_expiry'] ?? ''; ?>"
                        pattern="\d{2}/\d{4}" 
                        placeholder="MM/YYYY" 
                        required>
                </div>

                <div class="form-group form-group--half">
                    <label for="card_cvv">セキュリティコード</label>
                    <input type="text" id="card_cvv" name="card_cvv" 
                        value="<?php echo $user['card_cvv'] ?? ''; ?>"
                        pattern="\d{3,4}" 
                        placeholder="123" 
                        required>
                </div>
            </div>

            <div class="form-group">
                <label for="card_brand">カードブランド</label>
                <select id="card_brand" name="card_brand" required>
                    <option value="">選択してください</option>
                    <option value="visa" <?php echo $user['card_brand'] == 'visa' ? 'selected' : ''; ?>>VISA</option>
                    <option value="mastercard" <?php echo $user['card_brand'] == 'mastercard' ? 'selected' : ''; ?>>Mastercard</option>
                    <option value="jcb" <?php echo $user['card_brand'] == 'jcb' ? 'selected' : ''; ?>>JCB</option>
                    <option value="amex" <?php echo $user['card_brand'] == 'amex' ? 'selected' : ''; ?>>American Express</option>
                </select>
            </div>

            <div class="card-registration__buttons">
                <button type="submit" class="card-registration__button">登録する</button>
                <a href="/mypage" class="card-registration__button card-registration__button--cancel">キャンセル</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // カード番号の自動フォーマット
    const cardNumber = document.getElementById('card_number');
    cardNumber.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 16) value = value.slice(0, 16);
        const parts = value.match(/.{1,4}/g) || [];
        e.target.value = parts.join('-');
    });

    // 有効期限の自動フォーマット
    const cardExpiry = document.getElementById('card_expiry');
    cardExpiry.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 6) value = value.slice(0, 6);
        if (value.length > 2) {
            value = value.slice(0, 2) + '/' + value.slice(2);
        }
        e.target.value = value;
    });

    // セキュリティコードの制限
    const cardCvv = document.getElementById('card_cvv');
    cardCvv.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 4) value = value.slice(0, 4);
        e.target.value = value;
    });
});
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?> 