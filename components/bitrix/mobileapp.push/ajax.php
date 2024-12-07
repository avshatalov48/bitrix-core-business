<?
define("NO_KEEP_STATISTIC", true);
define('NO_AGENT_CHECK', true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('DisableEventsCheck', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CComponentUtil::__IncludeLang(dirname($_SERVER["SCRIPT_NAME"]), "/ajax.php");

if (!CModule::IncludeModule('mobileapp')) die(GetMessage('SMODE_MOBILEAPP_NOT_INSTALLED'));

$arResult = array();

if($USER->IsAuthorized() && check_bitrix_sessid())
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']): '';

	switch ($action)
	{
		case "save_options":
			$path = isset($_REQUEST['path']) ? trim($_REQUEST['path']): '';
			$arOptions = isset($_REQUEST['options']) ? $_REQUEST['options']: array();

			foreach (GetModuleEvents("mobileapp", "OnBeforeAdminMobilePushOptsSave", true) as $arHandler)
				ExecuteModuleEventEx($arHandler, array(
					$USER->GetID(),
					$path,
					&$arOptions
				));

			if(!empty($arOptions))
				if(!CAdminMobilePush::saveOptions($path, $arOptions))
					$arResult["ERROR"] = GetMessage("MOBILE_APP_PUSH_SAVE_OPTS_ERROR");

			break;
	}
}
else
{
	$arResult["ERROR"] = GetMessage("MOBILE_APP_PUSH_ACCESS_DENIED");
}

if(isset($arResult["ERROR"]))
	$arResult["RESULT"] = "ERROR";
else
	$arResult["RESULT"] = "OK";

die(json_encode($arResult));
