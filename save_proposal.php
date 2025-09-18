$filename = __DIR__ . '/data.csv'; // PHPのある場所と同じ場所に保存

// ファイルが無ければ作成
if (!file_exists($filename)) {
    $fp = fopen($filename, 'w');
    if ($fp) {
        $headers = [
            '入力日', '会社名', '会社住所', 'TEL', 'FAX',
            'ご担当者名', 'ご担当者TEL', 'ご担当者FAX', 'ご担当者メール',
            '発電所名', '発電所住所', '発電所TEL', '発電所FAX',
            '発電出力', '運開日', '年間売電量', 'チップ使用量', '年間売電金額',
            '未利用材コスト', '木質コスト', '建設廃材コスト', '売電金額/KWh',
            '所内消費電力量', '所内ピーク', '所内最低消費', '年間稼働時間',
            'ご要望'
        ];
        fputcsv($fp, $headers);
        fclose($fp);
        chmod($filename, 0666);
    } else {
        die('data.csvを作成できません。フォルダの権限を確認してください。');
    }
}
