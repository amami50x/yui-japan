<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

mb_language("Japanese");
mb_internal_encoding("UTF-8");

$filename = __DIR__ . "/contact.csv";

$labels = [
  "id-input_date"   => "ご記入日",
  "id-company_name" => "貴社名",
  "id-sechi-yubin"  => "所在地〒",
  "id-sechi-basyo"  => "所在地住所",
  "id-kinyu_name"   => "ご担当者名",
  "id-tanto_tel"    => "電話番号",
  "id-tanto_mail"   => "メールアドレス",
  "id-feedback"     => "ご質問・調査書内容など"
];

// POSTデータ取得
$data = [];
foreach ($_POST as $k => $v) {
  $data[$k] = trim($v);
}

try {
  // CSV保存処理
  $isNewFile = !file_exists($filename);
  $fp = fopen($filename, $isNewFile ? "w" : "a");
  if (!$fp) throw new Exception("CSVファイルを開けません");

  if ($isNewFile) {
    $header = ["処理年月日", "書き込み時間"];
    foreach ($labels as $label) {
      $header[] = $label;
    }
    $header = array_map(fn($h) => mb_convert_encoding($h, "SJIS-win", "UTF-8"), $header);
    fputcsv($fp, $header);
  }

  $row = [date("Y-m-d"), date("Y-m-d H:i:s")];
  foreach (array_keys($labels) as $key) {
    $value = $data[$key] ?? "";
    $value = str_replace(["\r\n", "\r", "\n"], "\r\n", $value);
    $row[] = $value;
  }
  $row = array_map(fn($v) => mb_convert_encoding($v, "SJIS-win", "UTF-8"), $row);
  fputcsv($fp, $row);
  fclose($fp);

  // ✉️ メール送信処理（ISO-2022-JPで文字化け対策）
  $to = 'info@yuijima.com';
  $from = 'amami50x@yuijima.com';

  // 本文作成
  $message = "お問い合わせ・調査書送信の入力内容です。\r\n\r\n以下の内容をご確認ください：\r\n\r\n";
  foreach ($labels as $key => $label) {
    $value = $data[$key] ?? '';
    $message .= "{$label}：{$value}\r\n";
  }
  $encoded_message = mb_convert_encoding($message, 'ISO-2022-JP', 'UTF-8');

  // 件名（Qエンコードで文字化け対策）
  $subject_raw = "HP-INPUT";
  $subject = mb_encode_mimeheader(mb_convert_encoding($subject_raw, 'ISO-2022-JP', 'UTF-8'), 'ISO-2022-JP', 'Q');

  // 差出人名（Qエンコード）
  $from_name_raw = "YUIJIMAシステム";
  $from_name = mb_encode_mimeheader(mb_convert_encoding($from_name_raw, 'ISO-2022-JP', 'UTF-8'), 'ISO-2022-JP', 'Q');

  // ヘッダー構成
  $headers = "From: {$from_name} <{$from}>\r\n";
  if (!empty($data['id-tanto_mail'])) {
    $headers .= "Reply-To: " . $data['id-tanto_mail'] . "\r\n";
  }
  $headers .= "Content-Type: text/plain; charset=ISO-2022-JP\r\n";

  // メール送信
  if (!mb_send_mail($to, $subject, $encoded_message, $headers)) {
    error_log("メール送信に失敗しました");
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'メール送信に失敗しました']);
  } else {
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
  }

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}