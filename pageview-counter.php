<?php
/**
 * サブページ ページオープンカウンタ（yui-japan）
 * GET  : 現在値を JSON で返す（加算しない・監視用）
 * POST : action=page_open&page=index-proposal|index-idsyosai|index-contact → +1
 * 保存: pageview-counts.json（flock）
 */
ini_set('display_errors', '0');
error_reporting(E_ALL);

$filename = __DIR__ . '/pageview-counts.json';
$allowed  = array('index-proposal', 'index-idsyosai', 'index-contact');
$defaults = array(
	'pages' => array(
		'index-proposal' => 0,
		'index-idsyosai' => 0,
		'index-contact'  => 0,
	),
	'updated' => null,
);
define('YUI_PV_FLOCK_TIMEOUT_SEC', 2.0);

function yui_pv_try_flock($fp, $operation) {
	$deadline = microtime(true) + YUI_PV_FLOCK_TIMEOUT_SEC;
	while (microtime(true) < $deadline) {
		if (flock($fp, $operation | LOCK_NB)) {
			return true;
		}
		usleep(50000);
	}
	return false;
}

function yui_pv_normalize($raw, $defaults, $allowed) {
	$data = json_decode((string) $raw, true);
	if (!is_array($data)) {
		$data = $defaults;
	}
	if (!isset($data['pages']) || !is_array($data['pages'])) {
		$data['pages'] = $defaults['pages'];
	}
	$pages = array();
	foreach ($allowed as $key) {
		$pages[$key] = isset($data['pages'][$key]) && is_numeric($data['pages'][$key])
			? (int) $data['pages'][$key] : 0;
	}
	$data['pages'] = $pages;
	$data['updated'] = isset($data['updated']) ? $data['updated'] : null;
	return $data;
}

function yui_pv_read_unlocked($filename, $defaults, $allowed) {
	$raw = @file_get_contents($filename);
	if ($raw === false || $raw === '') {
		return $defaults;
	}
	return yui_pv_normalize($raw, $defaults, $allowed);
}

function yui_pv_load($filename, $defaults, $allowed) {
	if (!file_exists($filename)) {
		@file_put_contents($filename, json_encode($defaults, JSON_UNESCAPED_UNICODE));
	}
	$fp = @fopen($filename, 'c+');
	if (!$fp) {
		return yui_pv_read_unlocked($filename, $defaults, $allowed);
	}
	$data = $defaults;
	if (yui_pv_try_flock($fp, LOCK_SH)) {
		rewind($fp);
		$raw = stream_get_contents($fp);
		$data = yui_pv_normalize($raw, $defaults, $allowed);
		flock($fp, LOCK_UN);
	} else {
		$data = yui_pv_read_unlocked($filename, $defaults, $allowed);
	}
	fclose($fp);
	return $data;
}

function yui_pv_update($filename, $defaults, $allowed, $page) {
	if (!file_exists($filename)) {
		@file_put_contents($filename, json_encode($defaults, JSON_UNESCAPED_UNICODE));
	}
	$fp = @fopen($filename, 'c+');
	if (!$fp) {
		return $defaults;
	}
	$data = $defaults;
	if (yui_pv_try_flock($fp, LOCK_EX)) {
		rewind($fp);
		$raw = stream_get_contents($fp);
		$data = yui_pv_normalize($raw, $defaults, $allowed);
		if (isset($data['pages'][$page])) {
			$data['pages'][$page] = (int) $data['pages'][$page] + 1;
		}
		$data['updated'] = gmdate('c');
		$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		ftruncate($fp, 0);
		rewind($fp);
		fwrite($fp, $json);
		fflush($fp);
		flock($fp, LOCK_UN);
	} else {
		$data = yui_pv_read_unlocked($filename, $defaults, $allowed);
	}
	fclose($fp);
	return $data;
}

function yui_pv_send($data) {
	header('Content-Type: application/json; charset=UTF-8');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function yui_pv_parse_body() {
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
	yui_pv_send(yui_pv_load($filename, $defaults, $allowed));
	exit;
}

if ($method === 'POST') {
	$body = yui_pv_parse_body();
	$action = isset($body['action']) ? (string) $body['action'] : '';
	$page   = isset($body['page']) ? (string) $body['page'] : '';
	if ($action === 'page_open' && in_array($page, $allowed, true)) {
		yui_pv_send(yui_pv_update($filename, $defaults, $allowed, $page));
		exit;
	}
	header('HTTP/1.1 400 Bad Request');
	yui_pv_send(array('error' => 'bad_request'));
	exit;
}

header('HTTP/1.1 405 Method Not Allowed');
yui_pv_send(array('error' => 'method_not_allowed'));