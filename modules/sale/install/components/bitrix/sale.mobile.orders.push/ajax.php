<?
define("NO_KEEP_STATISTIC", true);
define('NO_AGENT_CHECK', true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('DisableEventsCheck', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CComponentUtil::__IncludeLang(dirname($_SERVER["SCRIPT_NAME"]), "/ajax.php");

$arResult = array();

if($USER->IsAuthorized() && check_bitrix_sessid())
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']): '';

	switch ($action)
	{
		case "save_subs":
			$arSubs = isset($_REQUEST['subs']) ? $_REQUEST['subs']: array();

			if (!CModule::IncludeModule('sale'))
				$arResult["ERROR"] = GetMessage("SMOP_BC_NOT_INSTALLED");

			$userId = $USER->GetID();

			if(!CSaleMobileOrderPush::updateSubscriptions($userId, $arSubs))
				$arResult["ERROR"] = GetMessage("SMOP_SAVE_SUBS_ERROR");

			break;
	}
}
else
{
	$arResult["ERROR"] = GetMessage("SMOP_ACCESS_DENIED");
}

if(isset($arResult["ERROR"]))
	$arResult["RESULT"] = "ERROR";
else
	$arResult["RESULT"] = "OK";

$arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');
die(json_encode($arResult));
?>
