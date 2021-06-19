<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

define("START_EXEC_EPILOG_AFTER_1", microtime(true));
$GLOBALS["BX_STATE"] = "EA";

global $USER, $APPLICATION, $DB;

foreach(GetModuleEvents("main", "OnEpilog", true) as $arEvent)
	ExecuteModuleEventEx($arEvent);

$buffer = $APPLICATION->EndBufferContentMan();

//used in debug_info.php
$main_exec_time = round((getmicrotime()-START_EXEC_TIME), 4);

if(!defined("ADMIN_AJAX_MODE") && (($_REQUEST["mode"] ?? '') != 'excel'))
{
	//it's possible the method doesn't exist on update
	$session = null;
	$application = \Bitrix\Main\Application::getInstance();
	if(method_exists($application, 'getKernelSession'))
	{
		$session = $application->getKernelSession();
	}

	$canEditPHP = $USER->CanDoOperation('edit_php');
	$bShowTime = ($session && $session["SESS_SHOW_TIME_EXEC"] == 'Y');
	$bShowStat = ($DB->ShowSqlStat && $canEditPHP);
	$bShowCacheStat = (\Bitrix\Main\Data\Cache::getShowCacheStat() && ($canEditPHP || ($session && $session["SHOW_CACHE_STAT"] == "Y")));

	if($bShowTime || $bShowStat || $bShowCacheStat)
	{
		ob_start();
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/debug_info.php");
		$buffer .= ob_get_clean();
	}
}

CMain::FinalActions($buffer);
