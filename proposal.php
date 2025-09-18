<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 日本語メール設定
mb_language("Japanese");
mb_internal_encoding("UTF-8");

// CSVファイルパス
$filename = __DIR__ . '/proposal.csv';

// POSTデータ取得
$data = [];
foreach ($_POST as $k => $v) {
    $data[$k] = trim($v);
}

// 項目マッピング
$mapping = [
    'input_date' => '①入力日',
    'company_name' => '②会社名',
    'company_yubin' => '③会社〒',
    'company_address' => '③会社住所',
    'company_tel' => '④会社TEL',
    'company_fax' => '⑤会社FAX',
    'tanto_name' => '⑥ご担当者名',
    'tanto_tel' => '⑦ご担当者TEL',
    'tanto_fax' => '⑧ご担当者FAX',
    'tanto_mail' => '⑨ご担当者mail',
    'hatuden_name' => '⑩発電所名',
    'hatuden_yubin' => '⑪発電所〒',
    'hatuden_address' => '⑪発電所住所',
    'hatuden_tel' => '⑫発電所TEL',
    'hatuden_fax' => '⑬発電所FAX',
    'hatuden_syuturyoku' => '⑭発電出力（KW）',
    'unkai_date' => '⑮運開日',
    'nenkan_baiden_ryo' => '⑯年間売電電力量（平均KWh）',
    'chipsiyou_ave' => '⑰チップ燃料使用量（平均t/年）',
    'nenkan_baiden_gaku' => '⑱年間売電金額（平均円）',
    'chip_cost_miriyou' => '⑲燃料コスト（未利用材 円/t）',
    'chip_cost_mokusitu' => '⑳燃料コスト（一般木質 円/t）',
    'chip_cost_kensetu' => '㉑燃料コスト（建設廃材 円/t）',
    'baidenngaku_avr_kwh' => '㉒売電金額（平均 円/KWh）',
    'syonai_syohi_avr_kwh' => '㉓年間所内消費電力量（平均KWh）',
    'syonai_peek_kwh' => '㉔所内ピーク電力（KW）',
    'syonai_saitei_syohi_kw' => '㉕所内最低消費電力（KW）',
    'kadojikan_nenkan_avr_jikan' => '㉖年間稼働時間（平均 時間）',
    'feedback' => '㉗ご要望等ご記入ください'
];

try {
    // CSV書き込み処理
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
    $message = "YPS導入提案書の入力内容です。\r\n\r\n以下の内容が入力されました：\r\n\r\n";
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
    if (!empty($data['tanto_mail'])) {
        $headers .= "Reply-To: " . $data['tanto_mail'] . "\r\n";
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