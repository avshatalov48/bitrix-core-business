<?php
define("NO_KEEP_STATISTIC", true);
define('PUBLIC_AJAX_MODE', true);
define("NOT_CHECK_PERMISSIONS", true);

if (isset($_REQUEST['site_id']) && !empty($_REQUEST['site_id']))
{
	if (!is_string($_REQUEST['site_id']))
		die();
	if (preg_match('/^[a-z0-9_]{2}$/i', $_REQUEST['site_id']) === 1)
		define('site_id', $_REQUEST['site_id']);
}
else
{
	die();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if (!check_bitrix_sessid())
	die();

if (isset($_REQUEST['vote_id']) && is_string($_REQUEST['vote_id']) && !empty($_REQUEST['vote_id']))
{
	$result = array('success' => true, 'voted' => isset($_SESSION["IBLOCK_RATING"][$_REQUEST['vote_id']]));
	header('Content-Type: application/json');
	echo \Bitrix\Main\Web\Json::encode($result);
}

CMain::FinalActions();
