<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

mb_language("Japanese");
mb_internal_encoding("UTF-8");

// CSV保存先
$filename = __DIR__ . '/mail_requests.csv';

// POSTデータ取得
$user_id   = trim($_POST['user_id'] ?? '');
$user_name = trim($_POST['user_name'] ?? '');
$password  = trim($_POST['password'] ?? '');
$forwards  = trim($_POST['forwards'] ?? ''); // カンマ区切りで複数

// 入力チェック
$errors = [];
if ($user_id === '')   $errors[] = "＠前のアドレスを入力してください";
if ($user_name === '') $errors[] = "ユーザー名を入力してください";
if ($password === '')  $errors[] = "パスワードを入力してください";

if ($errors) {
    echo json_encode(['status' => 'error', 'errors' => $errors]);
    exit;
}

try {
    // CSVに保存（SJISで保存してExcelで開けるように）
    $is_new = !file_exists($filename);
    $fp = fopen($filename, $is_new ? 'w' : 'a');
    if (!$fp) throw new Exception("CSVファイルを開けません");

    if ($is_new) {
        $header = ['申請日時', '＠前のアドレス', 'ユーザー名', 'パスワード', '転送先'];
        $header = array_map(fn($h) => mb_convert_encoding($h, 'SJIS-win', 'UTF-8'), $header);
        fputcsv($fp, $header);
    }

    $row = [date('Y-m-d H:i:s'), $user_id, $user_name, $password, $forwards];
    $row = array_map(fn($v) => mb_convert_encoding($v, 'SJIS-win', 'UTF-8'), $row);
    fputcsv($fp, $row);
    fclose($fp);

    // 管理者にメール通知
    $to = 'admin@yuijima.com'; // 管理者
    $from = 'system@yuijima.com';

    $subject = mb_encode_mimeheader("【メール登録申請】{$user_id}@yuijima.com", "UTF-8", "B");

    $message = "以下の内容でメール登録申請がありました。\r\n\r\n";
    $message .= "＠前のアドレス：{$user_id}\r\n";
    $message .= "ユーザー名：{$user_name}\r\n";
    $message .= "パスワード：{$password}\r\n";
    $message .= "転送先：{$forwards}\r\n";

    $headers = "From: " . mb_encode_mimeheader("YUIJIMAシステム", "UTF-8", "B") . " <{$from}>\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    mb_send_mail($to, $subject, $message, $headers);

    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
