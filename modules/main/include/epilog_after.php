<?
define("B_EPILOG_INCLUDED", true);
define("START_EXEC_EPILOG_AFTER_1", microtime(true));
$GLOBALS["BX_STATE"] = "EA";

if(!isset($USER)) {global $USER;}
if(!isset($APPLICATION)) {global $APPLICATION;}
if(!isset($DB)) {global $DB;}

foreach(GetModuleEvents("main", "OnEpilog", true) as $arEvent)
	ExecuteModuleEventEx($arEvent);

if (\Bitrix\Main\ModuleManager::isModuleInstalled('translate'))
{
	if(isset($_GET["show_lang_files"]) || isset(\Bitrix\Main\Application::getInstance()->getSession()["SHOW_LANG_FILES"]))
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/lang_files.php");
}

$canEditPHP = $USER->CanDoOperation('edit_php');
if($canEditPHP)
	\Bitrix\Main\Application::getInstance()->getKernelSession()["SHOW_SQL_STAT"] = ($DB->ShowSqlStat? "Y": "N");

if(!defined('PUBLIC_AJAX_MODE') && (($_REQUEST["mode"] ?? '') != 'excel'))
{
	$bShowTime = isset(\Bitrix\Main\Application::getInstance()->getKernelSession()["SESS_SHOW_TIME_EXEC"]) && (\Bitrix\Main\Application::getInstance()->getKernelSession()["SESS_SHOW_TIME_EXEC"] == 'Y');
	$bShowStat = ($DB->ShowSqlStat && ($canEditPHP || \Bitrix\Main\Application::getInstance()->getKernelSession()["SHOW_SQL_STAT"]=="Y"));
	$bShowCacheStat = (\Bitrix\Main\Data\Cache::getShowCacheStat() && ($canEditPHP || \Bitrix\Main\Application::getInstance()->getKernelSession()["SHOW_CACHE_STAT"]=="Y"));

	if(($bShowStat || $bShowCacheStat) && !$USER->IsAuthorized())
	{
		require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/init_admin.php");
		$GLOBALS["APPLICATION"]->AddHeadString($GLOBALS["adminPage"]->ShowScript());
		$GLOBALS["APPLICATION"]->AddHeadString('<script type="text/javascript" src="/bitrix/js/main/public_tools.js"></script>');
		$GLOBALS["APPLICATION"]->AddHeadString('<link rel="stylesheet" type="text/css" href="/bitrix/themes/.default/pubstyles.css" />');
	}

	if($bShowTime || $bShowStat || $bShowCacheStat)
	{
		CUtil::InitJSCore(array('window', 'admin'));
	}
}

$buffer = $APPLICATION->EndBufferContentMan();

//used in debug_info.php
$main_exec_time = round(microtime(true) - START_EXEC_TIME, 4);

if(!defined('PUBLIC_AJAX_MODE') && (($_REQUEST["mode"] ?? '') != 'excel'))
{
	if($bShowTime || $bShowStat || $bShowCacheStat)
	{
		ob_start();
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/debug_info.php");
		$buffer .= ob_get_clean();
	}
}

CMain::FinalActions($buffer);
