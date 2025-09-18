<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>管理ページ</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 30px; }
    h1 { color: #333; }
    .menu { margin: 20px 0; }
    .menu a, .menu button {
      display: block;
      margin: 10px 0;
      padding: 10px 15px;
      background: #4CAF50;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      width: 250px;
      text-align: center;
    }
    .menu a:hover, .menu button:hover {
      background: #45a049;
    }
  </style>
</head>
<body>
  <h1>管理ページ</h1>
  <div class="menu">
    <!-- 1. HPを開く -->
    <a href="http://violetfoal2.sakura.ne.jp/yui-japan/index-yuijima0808.html" target="_blank">HPをOPEN</a>

    <!-- 2. メール処理 -->
    <a href="mail.php">メール処理</a>

    <!-- 3. データ（提案書） -->
    <a href="download.php?file=data.csv">提案書データをダウンロード</a>

    <!-- 4. データ（見積書） -->
    <a href="download.php?file=data-1.csv">見積書データをダウンロード</a>
  </div>
</body>
</html>
