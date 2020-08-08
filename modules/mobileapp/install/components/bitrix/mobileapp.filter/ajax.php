<?
define("NO_KEEP_STATISTIC", true);
define('NO_AGENT_CHECK', true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('DisableEventsCheck', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$arResult = array();
if (!CModule::IncludeModule('mobileapp'))
	$arResult["ERROR"] = 'module "mobileapp" not installed';

if($USER->IsAuthorized() && check_bitrix_sessid() && !isset($arResult["ERROR"]))
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']): '';

	switch ($action)
	{
		case "get_fields_html":
				$arFields = isset($_REQUEST['fields']) ? $_REQUEST['fields'] : array();
				$filterId = isset($_REQUEST['filter_id']) ? $_REQUEST['filter_id'] : '';

				if($filterId <> '' && !empty($arFields))
				{

					$arFieldsToSave = array();
					$arFields = $APPLICATION->ConvertCharsetArray($arFields, 'utf-8', SITE_CHARSET);

					foreach ($arFields as $fieldId => $arField)
						$arFieldsToSave[$fieldId] = $arField["VALUE"];

					CAdminMobileFilter::setFields($filterId, $arFieldsToSave);
					$arResult["HTML"] = CAdminMobileFilter::getHtml($arFields);
				}
				else
				{
					$arResult["ERROR"] = "Insufficient data";
				}

			break;
	}
}
else
{
	$arResult["ERROR"] = "Access denied";
}

if(isset($arResult["ERROR"]))
	$arResult["RESULT"] = "ERROR";
else
	$arResult["RESULT"] = "OK";

$arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');
die(json_encode($arResult));
?>
