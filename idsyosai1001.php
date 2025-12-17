<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

mb_language("Japanese");
mb_internal_encoding("UTF-8");

$filename = __DIR__ . '/idsyosai.csv';

$data = [];
foreach ($_POST as $k => $v) {
    $data[$k] = trim($v);
}

$mapping = [
    'id-input_date' => 'ご記入日',
    'id-company_name' => '貴社名',
    'id-kinyu_name' => '御記入者名',
    'id-tanto_tel' => 'お電話',
    'id-tanto_mail' => 'メールアドレス',
    'id-ninteibi' => '認定日',
    'id-setubi-id' => '設備ID',
    'id-sangyokyoku' => '担当経済産業局',
    'id-jigyosyamei' => '事業者名',
    'id-daihyosya-simei' => '代表者氏名',
    'id-setubi-kubun' => '発電設備の区分',
    'id-syuturyoku-ninteibi' => '発電設備の出力認定日',
    'id-setubi-meisho' => '発電設備の名称',
    'id-sechi-basyo' => '発電設備の設置場所',
    'id-setuzoku-keiyakubi' => '接続契約締結日',
    'id-feedback' => 'ご要望等ご記入ください'
];

try {
    // CSV保存処理
    $is_new = !file_exists($filename);
    $fp = fopen($filename, $is_new ? 'w' : 'a');
    if (!$fp) throw new Exception('CSVファイルを開けません');

    if ($is_new) {
        $header = ['処理年月日', '書き込み時間'];
        foreach ($mapping as $label) {
            $header[] = $label;
        }
        $header = array_map(fn($h) => mb_convert_encoding($h, 'SJIS-win', 'UTF-8'), $header);
        fputcsv($fp, $header);
    }

    $row = [date('Y-m-d'), date('Y-m-d H:i:s')];
    foreach (array_keys($mapping) as $key) {
        $value = $data[$key] ?? '';
        $value = str_replace(["\r\n", "\r", "\n"], "\r\n", $value);
        $row[] = $value;
    }
    $row = array_map(fn($v) => mb_convert_encoding($v, 'SJIS-win', 'UTF-8'), $row);
    fputcsv($fp, $row);
    fclose($fp);

    // ✉️ メール送信処理（ISO-2022-JPで文字化け対策）
    $to = 'info@yuijima.com';
    $from = 'amami50x@yuijima.com';

    // 本文作成
    $message = "ID詳細入力フォームの入力内容です。\r\n\r\n以下の内容をご確認ください：\r\n\r\n";
    foreach ($mapping as $key => $label) {
        $value = $data[$key] ?? '';
        $message .= "{$label}：{$value}\r\n";
    }
    $encoded_message = mb_convert_encoding($message, 'ISO-2022-JP', 'UTF-8');

    // 件名と差出人名（MIMEエンコード）
    $subject_raw = "HP-INPUT";
    $encoded_subject = mb_encode_mimeheader(mb_convert_encoding($subject_raw, 'ISO-2022-JP', 'UTF-8'), 'ISO-2022-JP');

    $from_name_raw = "YUIJIMAシステム";
    $encoded_from_name = mb_encode_mimeheader(mb_convert_encoding($from_name_raw, 'ISO-2022-JP', 'UTF-8'), 'ISO-2022-JP');

    // ヘッダー構成
    $headers = "From: {$encoded_from_name} <{$from}>\r\n";
    if (!empty($data['id-tanto_mail'])) {
        $headers .= "Reply-To: " . $data['id-tanto_mail'] . "\r\n";
    }
    $headers .= "Content-Type: text/plain; charset=ISO-2022-JP\r\n";

    // メール送信
    if (!mb_send_mail($to, $encoded_subject, $encoded_message, $headers)) {
        error_log("メール送信に失敗しました");
        echo json_encode(['status' => 'error', 'message' => 'メール送信に失敗しました']);
    } else {
        echo json_encode(['status' => 'ok']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}