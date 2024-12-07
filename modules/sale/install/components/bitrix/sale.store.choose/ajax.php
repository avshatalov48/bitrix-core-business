<?

use \Bitrix\Main\Config\Option;
/**
 * Bitrix Framework
 * @global CMain $APPLICATION
 */

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$result = array("ERROR" => "");

if (!\Bitrix\Main\Loader::includeModule('sale'))
	$result["ERROR"] = "Error! Can't include module \"Sale\"";

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if($result["ERROR"] == '' && $saleModulePermissions >= "W" && check_bitrix_sessid())
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']): '';

	switch ($action)
	{
		case "set_map_type":
			$mapType = isset($_REQUEST['map_type']) ? trim($_REQUEST['map_type']): 'yandex';
			Option::set('sale', 'order_choose_comp_map_type', $mapType);
			break;
		default:
			$result["ERROR"] = "Error! Wrong action!";
			break;
	}
}
else
{
	if($result["ERROR"] == '')
		$result["ERROR"] = "Error! Access denied";
}

if($result["ERROR"] <> '')
	$result["RESULT"] = "ERROR";
else
	$result["RESULT"] = "OK";

header('Content-Type: application/json');
echo json_encode($result);
\CMain::FinalActions();
die;