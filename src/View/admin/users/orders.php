<?php

require_once __DIR__ . '/../../layout/admin/header.php';

?>

<div class="admin-orders">
    <div class="admin-orders__container">
        <?php if (empty($orders)): ?>
            <div class="admin-orders__empty">
                <p>注文履歴がありません。</p>
            </div>
        <?php else: ?>
            <div class="order-items">
              <div class="admin-users__header">
                    <h1 class="admin-users__title">
                          注文履歴
                    </h1>
                    <a href="/admin/users" class="button button--secondary">
                        <i class="fas fa-arrow-left"></i>
                        ユーザー一覧に戻る
                    </a>
              </div>
              <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>注文番号</th>
                            <th>注文日時</th>
                            <th>合計金額</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= date('Y/m/d H:i', strtotime($order['created_at'])) ?></td>
                                <td>¥<?= number_format($order['total_amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right">合計</td>
                            <td class="total-amount">¥<?= number_format($order['total_amount']) ?></td>
                        </tr>
                    </tfoot>
                </table>

              </div>
            </div>
        <?php endif; ?>
    </div>
</div>
