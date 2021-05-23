<?
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
CComponentUtil::__IncludeLang(dirname($_SERVER["SCRIPT_NAME"]), "/ajax.php");

$arResult = array();

if (!CModule::IncludeModule("sale"))
	$arResult["ERROR"] = GetMessage("SMOB_SALE_NOT_INSTALLED");

if(!$USER->IsAdmin() || !check_bitrix_sessid())
	$arResult["ERROR"] = GetMessage("BCLMMD_ACCESS_DENIED");

if(!isset($arResult["ERROR"]))
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';

	switch ($action)
	{
		case 'check':

			$arBarCodeParams = array(
				"basketItemId" => isset($_POST["basketItemId"]) ? intval($_POST["basketItemId"]) : "",
				"barcode" => isset($_POST["barcode"]) ? $_POST["barcode"] : "",
				"productId" => isset($_POST["productId"]) ? intval($_POST["productId"]) : "",
				"productProvider" => isset($_POST["productProvider"]) ? $_POST["productProvider"] : "",
				"moduleName" => isset($_POST["moduleName"]) ? $_POST["moduleName"] : "",
				"barcodeMulti" => isset($_POST["barcodeMulti"]) && $_POST["barcodeMulti"] == "Y" ? "Y" : "N",
				"orderId" => isset($_POST["orderId"]) ? $_POST["orderId"] : "",
				"storeId" => isset($_POST["storeId"]) ? $_POST["storeId"] : array()
			);

			$checkResult = CSaleOrderHelper::isBarCodeValid($arBarCodeParams);

			$arResult["RESULT"] = $checkResult ? 'Y' : 'N';

			break;
	}
}

$arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');
die(json_encode($arResult));
?>
