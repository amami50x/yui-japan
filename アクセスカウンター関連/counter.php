<?php
$counter_file = "counter.txt";

// ファイルが存在しなければ自動作成
if (!file_exists($counter_file)) {
    $handle = fopen($counter_file, "w");
    if ($handle) {
        fwrite($handle, "0");
        fclose($handle);
        chmod($counter_file, 0666); // 書き込み可能に
    } else {
        die("counter.txt を作成できませんでした。");
    }
}

$count = (int)file_get_contents($counter_file);
$count++;
file_put_contents($counter_file, $count);
echo $count;
?>
