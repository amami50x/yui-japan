
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Tokyo');
$today = date('Ymd'); // 例：20250903
$zipFileName = "yui-data_{$today}.zip";

// 元ファイル名と処理日付きファイル名の対応表
$files = [
    'proposal.csv' => "proposal_{$today}.csv",
    'idsyosai.csv' => "idsyosai_{$today}.csv",
    'contact.csv'  => "contact_{$today}.csv"
];

// ZIP作成
$zipPath = __DIR__ . "/tmp/yui-data_{$today}.zip"; // tmpフォルダを事前に作成

$zip = new ZipArchive();
$zip->open($zipPath, ZipArchive::CREATE);

foreach ($files as $original => $renamed) {
    $fullPath = __DIR__ . "/{$original}";
    if (file_exists($fullPath)) {
        $zip->addFile($fullPath, $renamed); // ZIP内のファイル名を処理日付きに変更
    }
}

$zip->close();

// ヘッダー送信
header('Content-Type: application/zip');
header("Content-Disposition: attachment; filename=\"{$zipFileName}\"");
header('Content-Length: ' . filesize($zipPath));

// ZIP出力
readfile($zipPath);
unlink($zipPath); // 一時ファイル削除
exit;