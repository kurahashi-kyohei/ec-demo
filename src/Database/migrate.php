<?php

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/migrations/add_phone_and_address_to_users.php';

try {
    echo "マイグレーションを開始します...\n";
    
    // マイグレーションの実行
    $migration = new AddPhoneAndAddressToUsers();
    $migration->up();
    
    echo "マイグレーションが完了しました。\n";
} catch (Exception $e) {
    echo "エラーが発生しました: " . $e->getMessage() . "\n";
    exit(1);
} 