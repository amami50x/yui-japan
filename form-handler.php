<?php
// 保存先ファイル（用途ごとに分ける）
$file = __DIR__ . "/contact.csv";

// フォームからの値を取得
$company = $_POST['company'] ?? '';
$address = $_POST['address'] ?? '';
$name    = $_POST['name'] ?? '';
$phone   = $_POST['phone'] ?? '';
$email   = $_POST['email'] ?? '';
$message = $_POST['message'] ?? '';

// 日付を追加
$date = date("Y-m-d H:i:s");

// CSV用に配列化
$data = [$date, $company, $address, $name, $phone, $email, $message];

// ファイルに追記モードで保存
$fp = fopen($file, "a");
if ($fp) {
    fputcsv($fp, $data);
    fclose($fp);
}

// 送信完了後にトップへ戻す
header("Location: index-yuijima0808.html#top");
exit;
?>
