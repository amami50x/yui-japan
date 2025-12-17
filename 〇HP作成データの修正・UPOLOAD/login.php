
<?php
session_start();

// 管理者がここにユーザーを設定（少人数想定）
$users = [
    "tanaka" => "Pa55word!123",
    "suzuki" => "XyZ!98$klm"
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $pw = $_POST['pw'];

    if (isset($users[$id]) && $users[$id] === $pw) {
        $_SESSION['user'] = $id;
        header("Location: menu.php");
        exit;
    } else {
        $msg = "ログイン失敗。IDまたはパスワードが違います。";
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head><meta charset="UTF-8"><title>ログイン</title></head>
<body>
<h2>CSV管理ログイン</h2>
<form method="post">
  ID: <input type="text" name="id"><br>
  PASS: <input type="password" name="pw"><br>
  <button type="submit">ログイン</button>
</form>
<?php if(isset($msg)) echo "<p style='color:red;'>$msg</p>"; ?>
</body>
</html>
