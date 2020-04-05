<?php

if ($_SERVER['REQUEST_METHOD'] != 'POST')
{
	die;
}

if (isset($_POST['SITE_ID']) && is_string($_POST['SITE_ID']) && preg_match('/^[A-Za-z0-9_]{2}$/', $_POST['SITE_ID']) === 1)
{
	define('SITE_ID', $_POST['SITE_ID']);
}

define('STOP_STATISTICS', true);
define('NOT_CHECK_PERMISSIONS', true);
define('PUBLIC_AJAX_MODE', true);

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

if (! (check_bitrix_sessid() && Bitrix\Main\Loader::includeModule('conversion')))
{
	die;
}

if (($referer = $_POST['HTTP_REFERER']) && is_string($referer))
{
	$_SERVER['HTTP_REFERER'] = $referer;
}

$context = Bitrix\Conversion\DayContext::getInstance();
$context->saveInstance();
$context->addDayCounter('conversion_visit_day', 1);

echo 'OK';

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php';
