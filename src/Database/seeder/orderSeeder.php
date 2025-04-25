<?php
namespace App\Database\Seeder;

require_once __DIR__ . '/../../Config/Database.php';

use App\Config\Database;

class OrderSeeder {
    private $db;
    private $userIds = [1, 6, 7];  // 既存のユーザーIDを指定
    private $productIds = [6, 7, 8, 9, 10, 33, 35];  // 既存の商品IDを指定

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function run() {
        try {
            $this->db->beginTransaction();

            // 過去3年分のデータを生成
            $years = [
                date('Y'),      // 今年
                date('Y') - 1,  // 昨年
                date('Y') - 2   // 一昨年
            ];

            foreach ($years as $year) {
                // 各年10件のデータを生成
                for ($i = 0; $i < 10; $i++) {
                    // その年の1月から12月の間でランダムな日付を生成
                    $month = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
                    $day = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
                    $date = "{$year}-{$month}-{$day}";
                    
                    $userId = $this->userIds[array_rand($this->userIds)];
                    
                    // 注文を作成
                    $orderId = $this->createOrder($userId, $date);
                    
                    // 注文詳細を作成（1-3個のランダムな商品）
                    $this->createOrderDetails($orderId);
                }
            }

            $this->db->commit();
            echo "ダミーデータの作成が完了しました。\n";
            echo "今年（" . date('Y') . "年）: 10件\n";
            echo "昨年（" . (date('Y')-1) . "年）: 10件\n";
            echo "一昨年（" . (date('Y')-2) . "年）: 10件\n";
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo "エラーが発生しました: " . $e->getMessage() . "\n";
        }
    }

    private function createOrder($userId, $date) {
        $sql = "INSERT INTO orders (user_id, order_date, status, total_amount, created_at, updated_at) 
                VALUES (:user_id, :order_date, 'completed', 0, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':order_date' => $date
        ]);

        return $this->db->lastInsertId();
    }

    private function createOrderDetails($orderId) {
        $itemCount = rand(1, 3);
        $selectedProducts = array_rand($this->productIds, $itemCount);
        if (!is_array($selectedProducts)) {
            $selectedProducts = [$selectedProducts];
        }

        foreach ($selectedProducts as $productId) {
            $quantity = rand(1, 3);
            $price = rand(1000, 10000);

            $sql = "INSERT INTO order_details (order_id, product_id, quantity, price) 
                    VALUES (:order_id, :product_id, :quantity, :price)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':order_id' => $orderId,
                ':product_id' => $this->productIds[$productId],
                ':quantity' => $quantity,
                ':price' => $price
            ]);
        }

        // 注文の合計金額を更新
        $this->updateOrderTotal($orderId);
    }

    private function updateOrderTotal($orderId) {
        $sql = "UPDATE orders 
                SET total_amount = (
                    SELECT SUM(quantity * price) 
                    FROM order_details 
                    WHERE order_id = :order_id
                )
                WHERE id = :order_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
    }
}

// 実行
$seeder = new OrderSeeder();
$seeder->run();