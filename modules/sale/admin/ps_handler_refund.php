<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('sale');

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

\Bitrix\Main\Loader::includeModule('sale');

$APPLICATION->SetTitle(GetMessage('SALE_REFUND_HANDLERS_TITLE'));

$sTableID = "tbl_sale_ps_handler_refund";
$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$oSort = new CAdminUiSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$result = \Bitrix\Sale\PaySystem\Manager::getDataRefundablePage();

$dbRes = new CDBResult();
$dbRes->InitFromArray($result);

$dbRes = new CAdminUiResult($dbRes, $sTableID);
$dbRes->NavStart();

$lAdmin->SetNavigationParams($dbRes, array("BASE_LINK" => "/bitrix/admin/sale_ps_handler_refund.php"));

$lAdmin->AddHeaders(array(
	array("id" => "EXTERNAL_ID", "content" => GetMessage("SALE_REFUND_HANDLERS_LIST_EXTERNAL_ID"), "default" => true),
	array("id" => "NAME", "content" => GetMessage("SALE_REFUND_HANDLERS_LIST_NAME"), "default" => true),
	array("id" => "CONFIGURED", "content" => GetMessage("SALE_REFUND_HANDLERS_LIST_CONFIGURED"), "default" => true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arCCard = $dbRes->NavNext(false))
{
	$row =& $lAdmin->AddRow($arCCard["EXTERNAL_ID"], $arCCard, "sale_ps_handler_refund_edit.php?".$arCCard["LINK_PARAMS"]."&handler=".ToLower($arCCard["HANDLER"])."&lang=".LANGUAGE_ID);

	$row->AddField("EXTERNAL_ID", "<a href=\"sale_ps_handler_refund_edit.php?".$arCCard["LINK_PARAMS"].'&handler='.ToLower($arCCard["HANDLER"])."&lang=".LANGUAGE_ID."\">".$arCCard["EXTERNAL_ID"]."</a>");
	$row->AddField("NAME", htmlspecialcharsbx($arCCard["NAME"]));
	$row->AddField("CONFIGURED", GetMessage("SALE_REFUND_HANDLERS_LIST_CONFIGURED_".$arCCard["CONFIGURED"]));
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
