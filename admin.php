<?php
session_start();

// ==== 簡単なログイン認証 ====
$admin_user = "admin";
$admin_pass = "password123";

if (!isset($_SESSION['login'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['user'] === $admin_user && $_POST['pass'] === $admin_pass) {
            $_SESSION['login'] = true;
        } else {
            echo "<p style='color:red;'>ログイン失敗</p>";
        }
    }
    if (!isset($_SESSION['login'])) {
        echo '<form method="post">
                ユーザー名: <input type="text" name="user"><br>
                パスワード: <input type="password" name="pass"><br>
                <input type="submit" value="ログイン">
              </form>';
        exit;
    }
}

// ==== CSVファイルのパス ====
$filename = __DIR__ . "/data.csv";

// ==== ダウンロード処理 ====
if (isset($_GET['download'])) {
    if (file_exists($filename)) {
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"data.csv\"");
        header("Content-Length: " . filesize($filename));
        readfile($filename);
        exit;
    } else {
        echo "CSVファイルがまだありません。";
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>管理画面</title>
</head>
<body>
<h2>管理画面</h2>
<p><a href="?download=1">📥 CSVをダウンロード</a></p>
</body>
</html>
