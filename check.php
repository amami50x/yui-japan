
<?php
// PHPエラー表示設定
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 文字エンコーディング設定
mb_language("Japanese");
mb_internal_encoding("UTF-8");

// CSVファイルの保存先パスを定義
$csv_dir = __DIR__;
$filename = $csv_dir . '/idsyosai.csv';

$data = [];
// POSTデータを取得し、前後の空白を除去
foreach ($_POST as $k => $v) {
    $data[$k] = trim($v);
}

// 日本語ラベルのマッピング (CSV出力項目: 合計16項目)
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

// レスポンスのヘッダー設定
header('Content-Type: application/json; charset=UTF-8');

try {
    // ----------------------------------------------------
    // ⚠️ 必須チェックはクライアント側で担保されているため、サーバー側の強制的なスキップ処理は削除しました
    // ----------------------------------------------------
    
    // --- CSV保存処理 ---
    
    $is_new = !file_exists($filename);
    $fp = fopen($filename, 'a+'); 
    
    if (!$fp) {
        throw new Exception('CSVファイルを開けませんでした。権限とファイルパスを確認してください。');
    }
    
    // 新規ファイルの場合のみヘッダー行を書き込む
    if ($is_new) {
        $header = ['処理年月日', '書き込み時間']; // 2列
        foreach ($mapping as $label) {
            $header[] = $label; // 16列
        }
        // 合計18列のヘッダー
        $header_sjis = array_map(fn($h) => mb_convert_encoding($h, 'SJIS-win', 'UTF-8'), $header);
        fputcsv($fp, $header_sjis);
    }

    // データ行の作成と書き込み (全16項目 + 2タイムスタンプ = 18列)
    $row = [date('Y-m-d'), date('Y-m-d H:i:s')];
    
    foreach (array_keys($mapping) as $key) {
        // POSTデータにキーが存在しない場合は空文字列 '' を使用
        $value = $data[$key] ?? '';
        
        // CSVの列のズレを防ぐため、データ内の改行コードをスペースに置換
        $value = str_replace(["\r\n", "\r", "\n"], ' ', $value);
        
        $row[] = $value;
    }
    
    // SJIS-winに変換して書き込み
    $row_sjis = array_map(fn($v) => mb_convert_encoding($v, 'SJIS-win', 'UTF-8'), $row);
    fputcsv($fp, $row_sjis);
    fclose($fp);

    // ----------------------------------------------------
    // ✉️ メール送信処理
    // ----------------------------------------------------
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
    $subject_raw = "HP-INPUT ID詳細フォームより受付"; 
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
        // データは保存されているため、警告を返す
        echo json_encode(['status' => 'ok', 'warning' => 'データは保存されましたが、メール送信に失敗しました。']); 
    } else {
        echo json_encode(['status' => 'ok']);
    }

} catch (Exception $e) {
    // --- 致命的なエラーレスポンス ---
    http_response_code(500);
    $error_detail = $e->getMessage();
    error_log("致命的なエラー: " . $error_detail);
    echo json_encode(['status' => 'error', 'message' => 'サーバー処理中に致命的なエラーが発生しました。']);
}
?>
