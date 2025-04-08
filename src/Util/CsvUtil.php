<?php

namespace App\Util;

class CsvUtil
{
    /**
     * CSVファイルを読み込んで配列に変換
     */
    public static function readCsv(string $filepath, bool $hasHeader = true): array
    {
        $data = [];
        if (($handle = fopen($filepath, "r")) !== false) {
            // ファイルの内容を読み込んでエンコーディングを検出
            $content = file_get_contents($filepath);
            $encoding = mb_detect_encoding($content, ['UTF-8', 'SJIS', 'EUC-JP']);
            error_log('Detected encoding: ' . $encoding);

            // UTF-8以外の場合は変換を試みる
            if ($encoding && $encoding !== 'UTF-8') {
                try {
                    stream_filter_prepend($handle, "convert.iconv.{$encoding}/UTF-8");
                } catch (\Exception $e) {
                    error_log('Encoding conversion error: ' . $e->getMessage());
                }
            }
            
            if ($hasHeader) {
                $header = fgetcsv($handle, 0, ",", '"', "\\");
                error_log('CSV Headers: ' . print_r($header, true));
                while (($row = fgetcsv($handle, 0, ",", '"', "\\")) !== false) {
                    // 行データのデバッグ出力
                    error_log('CSV Row: ' . print_r($row, true));
                    if (count($header) === count($row)) {
                        $data[] = array_combine($header, $row);
                    } else {
                        error_log('Column count mismatch. Header: ' . count($header) . ', Row: ' . count($row));
                    }
                }
            } else {
                while (($row = fgetcsv($handle, 0, ",", '"', "\\")) !== false) {
                    $data[] = $row;
                }
            }
            fclose($handle);
        }
        return $data;
    }

    /**
     * データ配列をCSVファイルに出力
     */
    public static function writeCsv(array $data, string $filepath, array $headers = []): bool
    {
        try {
            // 一時ファイルを作成（UTF-8で書き込み）
            $tempFile = tempnam(sys_get_temp_dir(), 'csv_utf8_');
            $utf8Handle = fopen($tempFile, 'w');
            
            if ($utf8Handle === false) {
                error_log('Failed to open temporary file for writing');
                return false;
            }

            // UTF-8のBOMを書き込み
            fwrite($utf8Handle, pack('C*', 0xEF, 0xBB, 0xBF));

            // ヘッダーを書き込み（UTF-8）
            if (!empty($headers)) {
                if (fputcsv($utf8Handle, $headers, ',', '"', "\\", "\r\n") === false) {
                    error_log('Failed to write headers');
                    return false;
                }
            }

            // データを書き込み（UTF-8）
            foreach ($data as $row) {
                if (array_keys($row) !== range(0, count($row) - 1)) {
                    $row = array_values($row);
                }
                if (fputcsv($utf8Handle, $row, ',', '"', "\\", "\r\n") === false) {
                    error_log('Failed to write row');
                    return false;
                }
            }

            fclose($utf8Handle);

            // UTF-8からSJISに変換
            $content = file_get_contents($tempFile);
            $sjisContent = mb_convert_encoding($content, 'SJIS', 'UTF-8');
            
            // 最終的なファイルに書き込み
            if (file_put_contents($filepath, $sjisContent) === false) {
                error_log('Failed to write final SJIS file');
                return false;
            }

            // 一時ファイルを削除
            unlink($tempFile);
            
            return true;
        } catch (\Exception $e) {
            error_log('CSV write error: ' . $e->getMessage());
            if (isset($utf8Handle) && is_resource($utf8Handle)) {
                fclose($utf8Handle);
            }
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            return false;
        }
    }

    /**
     * CSVデータのバリデーション
     */
    public static function validateCsvData(array $data, array $rules): array
    {
        $errors = [];
        foreach ($data as $index => $row) {
            foreach ($rules as $field => $rule) {
                if (!isset($row[$field])) {
                    $errors[] = "行 " . ($index + 1) . ": {$field}フィールドが見つかりません。";
                    continue;
                }

                $value = $row[$field];
                
                // 必須チェック
                if (in_array('required', $rule) && empty($value)) {
                    $errors[] = "行 " . ($index + 1) . ": {$field}は必須です。";
                }
                
                // 数値チェック
                if (in_array('numeric', $rule) && !is_numeric($value)) {
                    $errors[] = "行 " . ($index + 1) . ": {$field}は数値である必要があります。";
                }
                
                // メールアドレスチェック
                if (in_array('email', $rule) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "行 " . ($index + 1) . ": {$field}は有効なメールアドレスである必要があります。";
                }
            }
        }
        return $errors;
    }
} 