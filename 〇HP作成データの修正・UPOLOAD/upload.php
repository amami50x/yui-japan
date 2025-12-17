
<?php
session_start();
if (!isset($_SESSION['user'])) { die("ログインしてください"); }

$files = [
    "file1" => "/home/xxx/www/data/form1.csv",
    "file2" => "/home/xxx/www/data/form2.csv",
    "file3" => "/home/xxx/www/data/form3.csv"
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = $_POST['file'];
    if (!isset($files[$key])) die("不正アクセス");

    $target = $files[$key];

    if (is_uploaded_file($_FILES['csv']['tmp_name'])) {
        // バックアップ保存
        if (file_exists($target)) {
            copy($target, $target . ".bak_" . date("Ymd_His"));
        }

        // 上書き
        move_uploaded_file($_FILES['csv']['tmp_name'], $target);
        echo "アップロード完了<br><a href='menu.php'>メニューに戻る</a>";
    } else {
        echo "アップロード失敗<br><a href='menu.php'>メニューに戻る</a>";
    }
}
