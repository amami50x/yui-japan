<?php
/**
 * yui-japan 必須ファイル・カウント確認（管理者のみ）
 * 認証は admin-auth.inc.php（admin.php と同じ値）
 */
session_start();
require __DIR__ . '/admin-auth.inc.php';

if (!isset($_SESSION['yui_health_login'])) {
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$user = isset($_POST['user']) ? (string) $_POST['user'] : '';
		$pass = isset($_POST['pass']) ? (string) $_POST['pass'] : '';
		if ($user === $admin_user && $pass === $admin_pass) {
			$_SESSION['yui_health_login'] = true;
		} else {
			echo "<p style='color:red;'>ログイン失敗</p>";
		}
	}
	if (!isset($_SESSION['yui_health_login'])) {
		header('Content-Type: text/html; charset=UTF-8');
		echo '<!DOCTYPE html><html lang="ja"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>yui-japan 管理確認</title></head><body>';
		echo '<h1>yui-japan 管理確認</h1>';
		echo '<form method="post">ユーザー名: <input type="text" name="user"><br>';
		echo 'パスワード: <input type="password" name="pass"><br>';
		echo '<input type="submit" value="ログイン"></form></body></html>';
		exit;
	}
}

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');

$required = array(
	'index.html',
	'counter.php',
	'counter.txt',
	'style.css',
	'script.js',
	'pageview-counter.php',
	'menu-click-counter.php',
	'yui-count.js',
	'admin-auth.inc.php',
	'index-proposal.html',
	'index-idsyosai.html',
	'index-contact.html',
	'images/logo-data-8.png',
	'images/top-page.webp',
	'images/catalog-002.webp',
	'images/QR-yui-japan.webp',
);

$missing = array();
$ok = array();
foreach ($required as $rel) {
	$path = __DIR__ . '/' . $rel;
	if (is_file($path)) {
		$ok[] = $rel;
	} else {
		$missing[] = $rel;
	}
}

$topCount = null;
$counterTxt = __DIR__ . '/counter.txt';
if (is_file($counterTxt)) {
	$raw = trim((string) @file_get_contents($counterTxt));
	if ($raw !== '' && is_numeric($raw)) {
		$topCount = (int) $raw;
	}
}

function yui_health_get_json($url) {
	$ctx = stream_context_create(array(
		'http' => array('method' => 'GET', 'timeout' => 3, 'ignore_errors' => true),
	));
	$raw = @file_get_contents($url, false, $ctx);
	if ($raw === false || $raw === '') {
		return null;
	}
	$data = json_decode($raw, true);
	return is_array($data) ? $data : null;
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$baseUrl = $scheme . '://' . $host . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
if ($baseUrl === $scheme . '://' . $host) {
	$baseUrl = $scheme . '://' . $host;
}

$pv = null;
$mc = null;
// 同一ディレクトリを直接読む（加算しない・ネットワーク不要）
$pvFile = __DIR__ . '/pageview-counts.json';
$mcFile = __DIR__ . '/menu-clicks.json';
if (is_file($pvFile)) {
	$pv = json_decode((string) file_get_contents($pvFile), true);
}
if (is_file($mcFile)) {
	$mc = json_decode((string) file_get_contents($mcFile), true);
}
// ファイルが無い場合は GET エンドポイント経由（加算なし）
if (!is_array($pv)) {
	$pv = yui_health_get_json($baseUrl . '/pageview-counter.php');
}
if (!is_array($mc)) {
	$mc = yui_health_get_json($baseUrl . '/menu-click-counter.php');
}

$allOk = (count($missing) === 0);
?><!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>yui-japan 必須ファイル・カウント確認</title>
<style>
body{font-family:sans-serif;margin:16px;line-height:1.5;max-width:720px}
.ok{color:#0a7a2f;font-weight:700}
.bad{color:#c00;font-weight:700}
.box{border:1px solid #ccc;border-radius:8px;padding:12px;margin:12px 0}
table{border-collapse:collapse;width:100%}
td,th{border:1px solid #ddd;padding:6px 8px;text-align:left;font-size:14px}
.warn{background:#fff3cd;padding:8px;border-radius:6px;font-size:13px}
</style>
</head>
<body>
<h1>yui-japan 必須ファイル・カウント確認</h1>
<div class="box">
<?php if ($allOk): ?>
  <p class="ok">必須ファイル：すべて正常（<?php echo count($ok); ?>件）</p>
<?php else: ?>
  <p class="bad">必須ファイル：不足あり（不足 <?php echo count($missing); ?> / 確認 <?php echo count($required); ?>）</p>
  <ul class="bad">
  <?php foreach ($missing as $m): ?>
    <li><?php echo htmlspecialchars($m, ENT_QUOTES, 'UTF-8'); ?></li>
  <?php endforeach; ?>
  </ul>
<?php endif; ?>
</div>

<div class="box">
<h2>カウント</h2>
<table>
<tr><th>項目</th><th>回数</th></tr>
<tr><td>TOP（counter.txt）</td><td><?php echo $topCount === null ? '（読めません）' : (int)$topCount; ?></td></tr>
<?php
$pages = (is_array($pv) && isset($pv['pages']) && is_array($pv['pages'])) ? $pv['pages'] : array();
foreach (array('index-proposal','index-idsyosai','index-contact') as $pkey) {
	$v = isset($pages[$pkey]) ? (int)$pages[$pkey] : 0;
	echo '<tr><td>ページオープン：' . htmlspecialchars($pkey, ENT_QUOTES, 'UTF-8') . '</td><td>' . $v . '</td></tr>';
}
$btn = (is_array($mc) && isset($mc['menu_button'])) ? (int)$mc['menu_button'] : 0;
echo '<tr><td>MENUボタン</td><td>' . $btn . '</td></tr>';
$items = (is_array($mc) && isset($mc['items']) && is_array($mc['items'])) ? $mc['items'] : array();
if ($items) {
	ksort($items);
	foreach ($items as $ik => $iv) {
		echo '<tr><td>MENU項目：' . htmlspecialchars((string)$ik, ENT_QUOTES, 'UTF-8') . '</td><td>' . (int)$iv . '</td></tr>';
	}
} else {
	echo '<tr><td>MENU項目</td><td>（まだなし）</td></tr>';
}
?>
</table>
</div>

<p class="warn">注意：TOPの counter.txt は直接URLでも読める状態のままです（既存監視のため変更していません）。新しい JSON（pageview-counts.json / menu-clicks.json）は .htaccess で直読み禁止です。数の取得は pageview-counter.php / menu-click-counter.php の GET を使ってください（加算されません）。</p>
<p><a href="index.html">TOPへ</a></p>
</body>
</html>