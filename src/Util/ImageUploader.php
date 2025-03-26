<?php

namespace App\Util;

class ImageUploader {
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    private $maxFileSize = 5242880; // 5MB

    public function __construct($uploadDir = null) {
        if ($uploadDir === null) {
            // アップロードディレクトリのパスを修正
            $this->uploadDir = __DIR__ . '/../public/uploads/products/';
            
            // ディレクトリが存在しない場合は作成
            if (!file_exists($this->uploadDir)) {
                mkdir($this->uploadDir, 0777, true);
            }
        } else {
            $this->uploadDir = $uploadDir;
        }
    }

    public function upload($file, $productName = null) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new \RuntimeException('Invalid parameters.');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new \RuntimeException('ファイルが選択されていません。');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new \RuntimeException('ファイルサイズが大きすぎます。');
            default:
                throw new \RuntimeException('Unknown errors.');
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new \RuntimeException('ファイルサイズは5MB以下にしてください。');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $this->allowedTypes)) {
            throw new \RuntimeException('許可されていないファイル形式です。');
        }

        $extension = array_search($mimeType, [
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ], true);

        if ($productName) {
            // 商品名から安全なファイル名を生成
            $baseName = $this->sanitizeFileName($productName);
            // 重複を避けるために現在のタイムスタンプを追加
            $fileName = sprintf('%s_%s.%s', $baseName, time(), $extension);
        } else {
            // 商品名が指定されていない場合は従来通りハッシュを使用
            $fileName = sprintf('%s.%s', sha1_file($file['tmp_name']), $extension);
        }

        if (!move_uploaded_file($file['tmp_name'], $this->uploadDir . $fileName)) {
            throw new \RuntimeException('ファイルのアップロードに失敗しました。');
        }

        return $fileName;
    }

    private function sanitizeFileName($fileName) {
        // 文字エンコーディングをUTF-8に統一
        $fileName = mb_convert_encoding($fileName, 'UTF-8', 'auto');
        
        // ファイル名として使用できない文字を除去
        $fileName = preg_replace('/[\\\\\/\:\*\?\"\<\>\|]/', '', $fileName);
        
        // スペースをアンダースコアに置換
        $fileName = str_replace(' ', '_', $fileName);
        
        // 文字数が長すぎる場合は切り詰める
        if (mb_strlen($fileName) > 32) {
            $fileName = mb_substr($fileName, 0, 32);
        }
        
        return $fileName;
    }

    public function delete($fileName) {
        $filePath = $this->uploadDir . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
            return true;
        }
        return false;
    }
} 