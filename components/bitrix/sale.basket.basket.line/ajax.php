<?php

define('STOP_STATISTICS', true);
define('NOT_CHECK_PERMISSIONS', true);

if (!isset($_POST['siteId']) || !is_string($_POST['siteId']))
	die();

if (!isset($_POST['templateName']) || !is_string($_POST['templateName']))
	die();

if ($_SERVER['REQUEST_METHOD'] != 'POST' ||
	preg_match('/^[A-Za-z0-9_]{2}$/', $_POST['siteId']) !== 1 ||
	preg_match('/^[.A-Za-z0-9_-]+$/', $_POST['templateName']) !== 1)
	die;

define('SITE_ID', $_POST['siteId']);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
if (!check_bitrix_sessid())
	die;

$template = trim($_POST['templateName']);
$params = isset($_POST['arParams']) && is_array($_POST['arParams']) ? $_POST['arParams'] : [];
$params['AJAX'] = 'Y';

$params = array_diff_key(
	$params,
	[
		'SEF_MODE' => true,
		'SEF_FOLDER' => true,
		'SEF_URL_TEMPLATES' => true,
	],
);

$APPLICATION->RestartBuffer();
header('Content-Type: text/html; charset='.LANG_CHARSET);
$APPLICATION->IncludeComponent('bitrix:sale.basket.basket.line', $template, $params);
