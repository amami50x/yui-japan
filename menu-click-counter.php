<?php
/**
 * MENUボタン／MENU項目クリックカウンタ（yui-japan）
 * GET  : 現在値を JSON で返す（加算しない・監視用）
 * POST : type=menu_button → ボタン +1
 *        type=menu_item&id=カタログ等のID → 項目 +1
 * 保存: menu-clicks.json（flock）
 */
ini_set('display_errors', '0');
error_reporting(E_ALL);

$filename = __DIR__ . '/menu-clicks.json';
$defaults = array(
	'menu_button' => 0,
	'items' => array(),
	'updated' => null,
);
define('YUI_MC_FLOCK_TIMEOUT_SEC', 2.0);

function yui_mc_try_flock($fp, $operation) {
	$deadline = microtime(true) + YUI_MC_FLOCK_TIMEOUT_SEC;
	while (microtime(true) < $deadline) {
		if (flock($fp, $operation | LOCK_NB)) {
			return true;
		}
		usleep(50000);
	}
	return false;
}

function yui_mc_valid_id($id) {
	return is_string($id)
		&& strlen($id) >= 1 && strlen($id) <= 60
		&& preg_match('/^[A-Za-z0-9_\-]+$/', $id) === 1;
}

function yui_mc_normalize($raw, $defaults) {
	$data = json_decode((string) $raw, true);
	if (!is_array($data)) {
		$data = $defaults;
	}
	$data['menu_button'] = isset($data['menu_button']) && is_numeric($data['menu_button'])
		? (int) $data['menu_button'] : 0;
	if (!isset($data['items']) || !is_array($data['items'])) {
		$data['items'] = array();
	}
	$items = array();
	foreach ($data['items'] as $k => $v) {
		if (!yui_mc_valid_id((string) $k)) {
			continue;
		}
		$items[(string) $k] = is_numeric($v) ? (int) $v : 0;
	}
	$data['items'] = $items;
	$data['updated'] = isset($data['updated']) ? $data['updated'] : null;
	return $data;
}

function yui_mc_read_unlocked($filename, $defaults) {
	$raw = @file_get_contents($filename);
	if ($raw === false || $raw === '') {
		return $defaults;
	}
	return yui_mc_normalize($raw, $defaults);
}

function yui_mc_load($filename, $defaults) {
	if (!file_exists($filename)) {
		@file_put_contents($filename, json_encode($defaults, JSON_UNESCAPED_UNICODE));
	}
	$fp = @fopen($filename, 'c+');
	if (!$fp) {
		return yui_mc_read_unlocked($filename, $defaults);
	}
	$data = $defaults;
	if (yui_mc_try_flock($fp, LOCK_SH)) {
		rewind($fp);
		$raw = stream_get_contents($fp);
		$data = yui_mc_normalize($raw, $defaults);
		flock($fp, LOCK_UN);
	} else {
		$data = yui_mc_read_unlocked($filename, $defaults);
	}
	fclose($fp);
	return $data;
}

function yui_mc_update($filename, $defaults, $updater) {
	if (!file_exists($filename)) {
		@file_put_contents($filename, json_encode($defaults, JSON_UNESCAPED_UNICODE));
	}
	$fp = @fopen($filename, 'c+');
	if (!$fp) {
		return $defaults;
	}
	$data = $defaults;
	if (yui_mc_try_flock($fp, LOCK_EX)) {
		rewind($fp);
		$raw = stream_get_contents($fp);
		$data = yui_mc_normalize($raw, $defaults);
		$data = $updater($data);
		$data = yui_mc_normalize(json_encode($data), $defaults);
		$data['updated'] = gmdate('c');
		$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		ftruncate($fp, 0);
		rewind($fp);
		fwrite($fp, $json);
		fflush($fp);
		flock($fp, LOCK_UN);
	} else {
		$data = yui_mc_read_unlocked($filename, $defaults);
	}
	fclose($fp);
	return $data;
}

function yui_mc_send($data) {
	header('Content-Type: application/json; charset=UTF-8');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	if (!isset($data['items']) || !is_array($data['items']) || count($data['items']) === 0) {
		$data['items'] = new stdClass();
	}
	echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function yui_mc_parse_body() {
	$out = array();
	if (!empty($_POST)) {
		return $_POST;
	}
	$raw = file_get_contents('php://input');
	if (is_string($raw) && $raw !== '') {
		parse_str($raw, $out);
	}
	return is_array($out) ? $out : array();
}

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

if ($method === 'GET') {
	yui_mc_send(yui_mc_load($filename, $defaults));
	exit;
}

if ($method === 'POST') {
	$body = yui_mc_parse_body();
	$type = isset($body['type']) ? (string) $body['type'] : '';
	if ($type === 'menu_button') {
		$data = yui_mc_update($filename, $defaults, function ($d) {
			$d['menu_button'] = (int) $d['menu_button'] + 1;
			return $d;
		});
		yui_mc_send($data);
		exit;
	}
	if ($type === 'menu_item') {
		$id = isset($body['id']) ? (string) $body['id'] : '';
		if (!yui_mc_valid_id($id)) {
			header('HTTP/1.1 400 Bad Request');
			yui_mc_send(array('error' => 'bad_id'));
			exit;
		}
		$data = yui_mc_update($filename, $defaults, function ($d) use ($id) {
			if (!isset($d['items'][$id])) {
				$d['items'][$id] = 0;
			}
			$d['items'][$id] = (int) $d['items'][$id] + 1;
			return $d;
		});
		yui_mc_send($data);
		exit;
	}
	header('HTTP/1.1 400 Bad Request');
	yui_mc_send(array('error' => 'bad_request'));
	exit;
}

header('HTTP/1.1 405 Method Not Allowed');
yui_mc_send(array('error' => 'method_not_allowed'));