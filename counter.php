<?php
$counter_file = "counter.txt";
if (!file_exists($counter_file)) {
    file_put_contents($counter_file, 0);
}
$count = (int)file_get_contents($counter_file);
$count++;
file_put_contents($counter_file, $count);
echo "<span style='font-size: 12px;'>$count</span>";
?>
