
<?php
session_start();
if (!isset($_SESSION['user'])) { die("ログインしてください"); }

$files = [
    "file1" => "/home/xxx/www/data/form1.csv",
    "file2" => "/home/xxx/www/data/form2.csv",
    "file3" => "/home/xxx/www/data/form3.csv"
];

$key = $_GET['file'] ?? "";
if (!isset($files[$key])) die("不正アクセス");

$path = $files[$key];
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="'.basename($path).'"');
readfile($path);
exit;
