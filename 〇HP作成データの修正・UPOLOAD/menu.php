
<?php
session_start();
if (!isset($_SESSION['user'])) { 
    header("Location: login.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head><meta charset="UTF-8"><title>CSV管理メニュー</title></head>
<body>
<h2>CSV管理メニュー（<?php echo htmlspecialchars($_SESSION['user']); ?>さん）</h2>

<!-- ダウンロード -->
<h3>CSVダウンロード</h3>
<a href="download.php?file=file1">Form1 ダウンロード</a><br>
<a href="download.php?file=file2">Form2 ダウンロード</a><br>
<a href="download.php?file=file3">Form3 ダウンロード</a><br>

<hr>

<!-- アップロード -->
<h3>CSVアップロード</h3>
<form action="upload.php" method="post" enctype="multipart/form-data">
  <select name="file">
    <option value="file1">Form1</option>
    <option value="file2">Form2</option>
    <option value="file3">Form3</option>
  </select>
  <input type="file" name="csv" accept=".csv">
  <button type="submit">アップロード</button>
</form>

<hr>
<a href="logout.php">ログアウト</a>
</body>
</html>
