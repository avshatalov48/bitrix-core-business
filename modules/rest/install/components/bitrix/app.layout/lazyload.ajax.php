<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

$siteID = isset($_REQUEST['site']) ? substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site']), 0, 2) : '';
if($siteID !== '')
{
	define('SITE_ID', $siteID);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if(!CModule::IncludeModule('rest') || !check_bitrix_sessid())
{
	die();
}

global $APPLICATION;
Header('Content-Type: text/html; charset='.LANG_CHARSET);
$APPLICATION->ShowAjaxHead();

$componentData = isset($_REQUEST['PARAMS']) && is_array($_REQUEST['PARAMS']) ? $_REQUEST['PARAMS'] : array();
$componentParams = isset($componentData['params']) && is_array($componentData['params']) ? $componentData['params'] : array();

$APPLICATION->IncludeComponent(
	'bitrix:app.layout',
	'',
	$componentParams,
	false,
	array('HIDE_ICONS' => 'Y')
);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
die();