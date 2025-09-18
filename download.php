
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$type = $_GET['type'] ?? '';
$filename = '';

switch ($type) {
  case 'proposal':
    $filename = __DIR__ . '/proposal.csv';
    break;
  case 'idsyosai':
    $filename = __DIR__ . '/idsyosai.csv';
    break;
  case 'contact':
    $filename = __DIR__ . '/contact.csv';
    break;
  default:
    http_response_code(400);
    echo "不正なリクエストです";
    exit;
}

if (!file_exists($filename)) {
  http_response_code(404);
  echo "ファイルが存在しません";
  exit;
}

// ダウンロードヘッダー（Shift_JISで文字化け防止）
header("Content-Type: text/csv; charset=Shift_JIS");
header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode(basename($filename)));
header("Content-Length: " . filesize($filename));
readfile($filename);
exit;