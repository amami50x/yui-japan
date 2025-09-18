<?php
$filename = "counter.txt";

// ファイルがなければ初期化
if (!file_exists($filename)) {
  file_put_contents($filename, "0");
}

// カウンター数を読み込み
$count = (int)file_get_contents($filename);

// カウントアップして保存
$count++;
file_put_contents($filename, (string)$count);
echo "<div style='font-size:14px;'>訪問者数：{$count}</div>";
?>
