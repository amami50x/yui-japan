
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
mb_language("Japanese");
mb_internal_encoding("UTF-8");

$csv_dir = __DIR__;
$filename = $csv_dir . '/idsyosai.csv';

// POSTデータ取得と整形
$data = [];
foreach ($_POST as $k => $v) {
    $data[$k] = trim($v);
}

// CSV列マッピング
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

// ヘッダーは必ず最初に設定
header('Content-Type: application/json; charset=UTF-8');

try {
    // ----------------------------------------------------
    // 🚨 必須チェック（最優先で実行し、失敗時は即座に処理を終了させる）
    // ----------------------------------------------------
    $required_keys = array_diff(array_keys($mapping), ['id-feedback']);
    $missing = [];
    foreach ($required_keys as $key) {
        // データが存在しない、または空文字列かチェック
        if (!isset($data[$key]) || $data[$key] === '') {
            $missing[] = $mapping[$key];
        }
    }

    if (!empty($missing)) {
        // 空欄がある場合はCSVへ書き込まずエラー応答して処理を終了
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => '必須項目が未入力です: ' . implode(', ', $missing)
        ]);
        exit; // 必須チェック失敗時にここで終了
    }

    // ----------------------------------------------------
    // CSV書き込み（必須チェック通過後のみ実行）
    // ----------------------------------------------------
    $is_new = !file_exists($filename);
    
    // 'a' モード（追記専用）に変更することで、新規ファイル作成時でも
    // データポインターの意図しない移動を防ぎ、安全性を高めます。
    $fp = fopen($filename, 'a'); 
    if (!$fp) throw new Exception('CSVファイルを開けませんでした。');

    if ($is_new) {
        $header = ['処理年月日', '書き込み時間'];
        foreach ($mapping as $label) $header[] = $label;
        $header_sjis = array_map(fn($h) => mb_convert_encoding($h, 'SJIS-win', 'UTF-8'), $header);
        fputcsv($fp, $header_sjis);
    }

    // データ行の準備
    $row = [date('Y-m-d'), date('Y-m-d H:i:s')];
    foreach (array_keys($mapping) as $key) {
        // 値が存在しない場合は空文字列をセットし、改行をスペースに置換
        $value = str_replace(["\r\n", "\r", "\n"], ' ', $data[$key] ?? '');
        $row[] = $value;
    }

    // SJISに変換して書き込み
    $row_sjis = array_map(fn($v) => mb_convert_encoding($v, 'SJIS-win', 'UTF-8'), $row);
    fputcsv($fp, $row_sjis);
    fclose($fp);

    // ----------------------------------------------------
    // ✉️ メール送信（必須チェック通過後のみ実行）
    // ----------------------------------------------------
    $to = 'info@yuijima.com';
    $from = 'amami50x@yuijima.com';

    $message = "ID詳細入力フォームの入力内容です。\r\n\r\n";
    foreach ($mapping as $key => $label) {
        $message .= "{$label}：" . ($data[$key] ?? '') . "\r\n";
    }
    $encoded_message = mb_convert_encoding($message, 'ISO-2022-JP', 'UTF-8');

    $subject_raw = "HP-INPUT ID詳細フォームより受付";
    $encoded_subject = mb_encode_mimeheader(mb_convert_encoding($subject_raw, 'ISO-2022-JP', 'UTF-8'), 'ISO-2022-JP');

    $from_name_raw = "YUIJIMAシステム";
    $encoded_from_name = mb_encode_mimeheader(mb_convert_encoding($from_name_raw, 'ISO-2022-JP', 'UTF-8'), 'ISO-2022-JP');

    $headers = "From: {$encoded_from_name} <{$from}>\r\n";
    if (!empty($data['id-tanto_mail'])) {
        $headers .= "Reply-To: " . $data['id-tanto_mail'] . "\r\n";
    }
    $headers .= "Content-Type: text/plain; charset=ISO-2022-JP\r\n";

    if (!mb_send_mail($to, $encoded_subject, $encoded_message, $headers)) {
        echo json_encode(['status' => 'ok', 'warning' => 'データ保存成功、メール送信失敗']);
    } else {
        echo json_encode(['status' => 'ok']);
    }

} catch (Exception $e) {
    // 致命的なサーバーエラー（ファイルオープン失敗など）の処理
    http_response_code(500);
    error_log("致命的エラー: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'サーバー処理中にエラーが発生しました。']);
}
?>
