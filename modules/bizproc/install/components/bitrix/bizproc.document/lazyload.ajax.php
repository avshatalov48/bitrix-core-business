<?php

define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

$siteID = isset($_REQUEST['site'])? mb_substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site']), 0, 2) : '';
if($siteID !== '')
{
	define('SITE_ID', $siteID);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!check_bitrix_sessid() || !CModule::IncludeModule('bizproc'))
{
	die();
}

$componentData = isset($_REQUEST['PARAMS']) && is_array($_REQUEST['PARAMS']) ? $_REQUEST['PARAMS'] : [];
$params = isset($componentData['params']) && is_array($componentData['params']) ? $componentData['params'] : [];

$componentParams = [];
$componentParams['MODULE_ID'] = $params['MODULE_ID'] ?? null;
$componentParams['ENTITY'] = $params['ENTITY'] ?? null;
$componentParams['DOCUMENT_TYPE'] = $params['DOCUMENT_TYPE'] ?? null;
$componentParams['DOCUMENT_ID'] = $params['DOCUMENT_ID'] ?? null;

//For custom reload with params
$ajaxLoaderParams = array(
	'url' => '/bitrix/components/bitrix/bizproc.document/lazyload.ajax.php?&site='.SITE_ID.'&'.bitrix_sessid_get(),
	'method' => 'POST',
	'dataType' => 'ajax',
	'data' => array('PARAMS' => $componentData)
);

global $APPLICATION;
Header('Content-Type: text/html; charset='.LANG_CHARSET);
$APPLICATION->ShowAjaxHead();

//Force AJAX mode
$componentParams['AJAX_MODE'] = 'Y';
$componentParams['AJAX_OPTION_JUMP'] = 'N';
$componentParams['AJAX_OPTION_HISTORY'] = 'N';
$componentParams['AJAX_LOADER'] = $ajaxLoaderParams;
$componentParams['LAZYLOAD'] = 'Y';

$APPLICATION->IncludeComponent('bitrix:bizproc.document',
	$componentData['template'] ?? '',
	$componentParams,
	false,
	array('HIDE_ICONS' => 'Y', 'ACTIVE_COMPONENT'=>'Y')
);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
die();