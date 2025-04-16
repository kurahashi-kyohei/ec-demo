<?php

class AddSocialLoginToUsers
{
    public function up()
    {
        try {
            $pdo = require __DIR__ . '/../connection.php';
            $pdo->beginTransaction();

            // is_social_loginカラムを追加
            $sql = "ALTER TABLE users ADD COLUMN is_social_login BOOLEAN DEFAULT FALSE";
            $pdo->exec($sql);

            $pdo->commit();
            echo "Added is_social_login column successfully.\n";
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

    public function down()
    {
        try {
            $pdo = require __DIR__ . '/../connection.php';
            $pdo->beginTransaction();

            // is_social_loginカラムを削除
            $sql = "ALTER TABLE users DROP COLUMN is_social_login";
            $pdo->exec($sql);

            $pdo->commit();
            echo "Removed is_social_login column successfully.\n";
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
} 