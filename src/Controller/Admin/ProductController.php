<?php

namespace App\Controller\Admin;

use App\Model\Product;
use App\Middleware\SessionMiddleware;
use App\Util\ImageUploader;
use App\Util\CsvUtil;

class ProductController
{
    private $productModel;
    private $imageUploader;

    public function __construct()
    {
        // セッションが開始されていない場合は開始
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 管理者権限チェック
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit();
        }

        $this->productModel = new Product();
        $this->imageUploader = new ImageUploader();
    }

    public function index()
    {
        try {
            // ソートパラメータの取得
            $sort = $_GET['sort'] ?? 'id';
            $order = $_GET['order'] ?? 'asc';
            
            // 現在のページ番号を取得
            $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $perPage = 30;
            
            // 検索パラメータの取得
            $totalProducts = $this->productModel->getTotalProducts();
            $totalPages = ceil($totalProducts / $perPage);
            $offset = ($currentPage - 1) * $perPage;
            $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : null;
            $category = isset($_GET['category']) ? $_GET['category'] : null;
            
            // 商品データの取得
            $products = $this->productModel->searchProducts($currentPage, $keyword, $category, $sort, $order);
            // $products = $this->productModel->searchProducts($currentPage, $keyword, $category);
            $categories = $this->productModel->getCategories();
            
            // 総商品数とページ数の計算
            $totalProducts = $this->productModel->getTotalProducts();
            $totalPages = ceil($totalProducts / $perPage);
            
            $data = [
                'title' => '商品管理',
                'products' => $products,
                'categories' => $categories,
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'perPage' => $perPage,
                'totalProducts' => $totalProducts,
                'currentSort' => $sort,
                'currentOrder' => $order,
                'totalProducts' => $totalProducts
            ];
            
            extract($data);
            require __DIR__ . '/../../View/admin/products/index.php';
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            require __DIR__ . '/../../View/error/500.php';
        }
    }

    public function create()
    {
        $data = [
            'title' => '商品登録'
        ];

        extract($data);
        require __DIR__ . '/../../View/admin/products/create.php';
    }

    public function store()
    {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $category = $_POST['category'] ?? '';

        if (empty($name) || empty($description) || empty($category)) {
            $_SESSION['error'] = '必須項目を入力してください。';
            header('Location: /admin/products/create');
            exit();
        }

        if($this->productModel->findByName($name)) {
            $_SESSION['error'] = '同じ商品名が存在します。';
            header('Location: /admin/products/create');
            exit();
        }

        $imagePath = '';
        try {
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $fileName = $this->imageUploader->upload($_FILES['image'], $name);
                $imagePath = '/uploads/products/' . $fileName;
            }
        } catch (\RuntimeException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /admin/products/create');
            exit();
        }

        $result = $this->productModel->create([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'image_path' => $imagePath,
            'category' => $category
        ]);

        if ($result) {
            $_SESSION['success'] = '商品を登録しました。';
            header('Location: /admin/products');
            exit();
        } else {
            $_SESSION['error'] = '商品の登録に失敗しました。';
            header('Location: /admin/products/create');
            exit();
        }
    }

    public function importCsv()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/products');
            exit();
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'CSVファイルのアップロードに失敗しました。';
            header('Location: /admin/products');
            exit();
        }

        $tmpName = $_FILES['csv_file']['tmp_name'];
        
        try {
            $data = CsvUtil::readCsv($tmpName);
            
            // デバッグ情報
            error_log('CSV Data: ' . print_r($data, true));
            
            // バリデーションルール
            $rules = [
                'name' => ['required'],
                'description' => ['required'],
                'price' => ['required', 'numeric'],
                'stock' => ['required', 'numeric'],
                'category' => ['required']
            ];
            
            // バリデーション実行
            $errors = CsvUtil::validateCsvData($data, $rules);

            if($this->productModel->findByName($data['name'])) {
                $_SESSION['error'] = '同じ商品名が存在します。';
                header('Location: /admin/products');
                exit();
            }
            
            if (!empty($errors)) {
                error_log('Validation Errors: ' . print_r($errors, true));
                $_SESSION['error'] = implode("<br>", $errors);
                header('Location: /admin/products');
                exit();
            }
            
            // データをインポート
            $success = 0;
            $failed = 0;
            foreach ($data as $row) {
                try {
                    $result = $this->productModel->create([
                        'name' => $row['name'],
                        'description' => $row['description'],
                        'price' => $row['price'],
                        'stock' => $row['stock'],
                        'category' => $row['category'],
                        'image_path' => $row['image_path'] ?? null
                    ]);
                    
                    if ($result) {
                        $success++;
                    } else {
                        $failed++;
                        error_log('Failed to create product: ' . print_r($row, true));
                    }
                } catch (\Exception $e) {
                    $failed++;
                    error_log('Exception while creating product: ' . $e->getMessage());
                    error_log('Product data: ' . print_r($row, true));
                }
            }
            
            if ($failed > 0) {
                $_SESSION['warning'] = "{$success}件の商品を登録しました。{$failed}件の登録に失敗しました。";
            } else {
                $_SESSION['success'] = "{$success}件の商品を登録しました。";
            }
        } catch (\Exception $e) {
            error_log('CSV Import Error: ' . $e->getMessage());
            $_SESSION['error'] = 'CSVのインポート中にエラーが発生しました。';
        }
        
        header('Location: /admin/products');
        exit();
    }

    /**
     * 商品データをCSVファイルとしてエクスポート
     */
    public function exportCsv()
    {
        try {
            $products = $this->productModel->getAllProducts();
            error_log('Exporting products: ' . count($products) . ' items');
            
            // データを整形（UTF-8で処理）
            $exportData = [];
            foreach ($products as $product) {
                // 各フィールドがUTF-8であることを確認
                $exportData[] = [
                    'id' => $product['id'],
                    'name' => mb_convert_encoding($product['name'], 'UTF-8', 'auto'),
                    'description' => mb_convert_encoding($product['description'], 'UTF-8', 'auto'),
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'category' => mb_convert_encoding($product['category'], 'UTF-8', 'auto'),
                    'image_path' => $product['image_path'],
                    'created_at' => $product['created_at'],
                    'updated_at' => $product['updated_at']
                ];
            }
            
            // CSVヘッダー
            $headers = ['id', 'name', 'description', 'price', 'stock', 'category', 'image_path', 'created_at', 'updated_at'];
            
            // 一時ファイルを作成
            $tempFile = tempnam(sys_get_temp_dir(), 'products_');
            error_log('Creating temp file: ' . $tempFile);
            
            if (CsvUtil::writeCsv($exportData, $tempFile, $headers)) {
                // ファイルサイズを確認
                $fileSize = filesize($tempFile);
                error_log('CSV file size: ' . $fileSize . ' bytes');
                
                if ($fileSize === 0) {
                    throw new \Exception('Generated CSV file is empty');
                }
                
                // ダウンロード用ヘッダーを設定
                header('Content-Type: text/csv; charset=Shift_JIS');
                header('Content-Disposition: attachment; filename="products_' . date('Ymd_His') . '.csv"');
                header('Content-Length: ' . $fileSize);
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
                
                // ファイルを出力
                readfile($tempFile);
                
                // 一時ファイルを削除
                unlink($tempFile);
                exit();
            } else {
                throw new \Exception('Failed to write CSV file');
            }
        } catch (\Exception $e) {
            error_log('CSV Export Error: ' . $e->getMessage());
            $_SESSION['error'] = 'CSVファイルの生成に失敗しました。';
            header('Location: /admin/products');
            exit();
        }
    }

    public function edit($id)
    {
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            $_SESSION['error'] = '商品が見つかりませんでした。';
            header('Location: /admin/products');
            exit();
        }

        $data = [
            'title' => '商品編集',
            'product' => $product
        ];

        extract($data);
        require __DIR__ . '/../../View/admin/products/edit.php';
    }

    public function update($id)
    {
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            $_SESSION['error'] = '商品が見つかりませんでした。';
            header('Location: /admin/products');
            exit();
        }

        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $category = $_POST['category'] ?? '';

        // バリデーション
        if (empty($name) || empty($description) || empty($category)) {
            $_SESSION['error'] = '必須項目を入力してください。';
            header('Location: /admin/products/edit/' . $id);
            exit();
        }

        // 既存の画像パスを保持
        $imagePath = $product['image_path'];

        // 新しい画像がアップロードされた場合
        try {
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                // 古い画像を削除
                if (!empty($product['image_path'])) {
                    $oldFileName = basename($product['image_path']);
                    $this->imageUploader->delete($oldFileName);
                }

                $fileName = $this->imageUploader->upload($_FILES['image'], $name);
                $imagePath = '/uploads/products/' . $fileName;
            }
        } catch (\RuntimeException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /admin/products/edit/' . $id);
            exit();
        }

        $result = $this->productModel->update($id, [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'image_path' => $imagePath,
            'category' => $category
        ]);

        if ($result) {
            $_SESSION['success'] = '商品を更新しました。';
            header('Location: /admin/products');
            exit();
        } else {
            $_SESSION['error'] = '商品の更新に失敗しました。';
            header('Location: /admin/products/edit/' . $id);
            exit();
        }
    }

    public function delete($id)
    {
        try {
            $product = $this->productModel->findById($id);
            if (!$product) {
                $_SESSION['error'] = '商品が見つかりませんでした。';
                header('Location: /admin/products');
                exit;
            }

            $this->productModel->delete($id);
            $_SESSION['success'] = '商品を削除しました。';
        } catch (Exception $e) {
            $_SESSION['error'] = '商品の削除に失敗しました。';
            error_log($e->getMessage());
        }

        header('Location: /admin/products');
        exit;
    }

    public function bulkDelete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit;
        }

        // CSRFトークンの検証（実装されている場合）
        if (function_exists('verify_csrf_token')) {
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'セキュリティトークンが無効です。';
                header('Location: /admin/products');
                exit;
            }
        }

        $productIds = $_POST['product_ids'] ?? [];
        if (empty($productIds)) {
            $_SESSION['error'] = '削除する商品が選択されていません。';
            header('Location: /admin/products');
            exit;
        }

        try {
            $deletedCount = 0;
            $failedIds = [];

            foreach ($productIds as $id) {
                try {
                    $product = $this->productModel->findById($id);
                    if (!$product) {
                        $failedIds[] = $id;
                        continue;
                    }

                    if ($this->productModel->delete($id)) {
                        $deletedCount++;
                    } else {
                        $failedIds[] = $id;
                    }
                } catch (\Exception $e) {
                    error_log("Error deleting product ID {$id}: " . $e->getMessage());
                    $failedIds[] = $id;
                }
            }

            if ($deletedCount > 0) {
                $_SESSION['success'] = $deletedCount . '件の商品を削除しました。';
                if (!empty($failedIds)) {
                    $_SESSION['warning'] = count($failedIds) . '件の商品は削除できませんでした。';
                    error_log('Failed to delete products: ' . implode(', ', $failedIds));
                }
            } else {
                $_SESSION['error'] = '商品の削除に失敗しました。';
                error_log('All product deletions failed. IDs: ' . implode(', ', $failedIds));
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = '商品の削除中にエラーが発生しました。';
            error_log('Bulk delete error: ' . $e->getMessage());
        }

        header('Location: /admin/products');
        exit;
    }
} 