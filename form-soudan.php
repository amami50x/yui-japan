
<?php
// 日本語を正しく扱う
header("Content-Type: text/html; charset=UTF-8");

// 保存先ファイル名
$file = "soudan-data.csv";

// POSTデータ取得（存在しない場合は空文字）
$data = [
    $_POST['entry_date'] ?? '',
    $_POST['company_name'] ?? '',
    $_POST['contact_name'] ?? '',
    $_POST['phone'] ?? '',
    $_POST['email'] ?? '',
    $_POST['certification_date'] ?? '',
    $_POST['equipment_id'] ?? '',
    $_POST['bureau'] ?? '',
    $_POST['operator_name'] ?? '',
    $_POST['representative_name'] ?? '',
    $_POST['equipment_type'] ?? '',
    $_POST['equipment_output'] ?? '',
    $_POST['equipment_name'] ?? '',
    $_POST['installation_location'] ?? '',
    $_POST['connection_date'] ?? '',
    $_POST['requests'] ?? ''
];

// CSVに追記モードで保存
$fp = fopen($file, "a");
if ($fp !== false) {
    fputcsv($fp, $data);
    fclose($fp);
    echo "<h2>送信が完了しました。ありがとうございました。</h2>";
    echo "<p><a href='form.html'>← フォームに戻る</a></p>";
} else {
    echo "<h2>エラー: データを保存できませんでした。</h2>";
}
?>
