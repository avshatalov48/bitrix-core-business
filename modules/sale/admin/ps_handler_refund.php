<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

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

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$result = \Bitrix\Sale\PaySystem\Manager::getDataRefundablePage();

$dbRes = new CDBResult();
$dbRes->InitFromArray($result);

$dbRes = new CAdminResult($dbRes, $sTableID);
$dbRes->NavStart();

$lAdmin->NavText($dbRes->GetNavPrint(GetMessage("SALE_PRLIST")));

$lAdmin->AddHeaders(array(
	array("id" => "EXTERNAL_ID", "content" => GetMessage("SALE_REFUND_HANDLERS_LIST_EXTERNAL_ID"), "default" => true),
	array("id" => "NAME", "content" => GetMessage("SALE_REFUND_HANDLERS_LIST_NAME"), "default" => true),
	array("id" => "CONFIGURED", "content" => GetMessage("SALE_REFUND_HANDLERS_LIST_CONFIGURED"), "default" => true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arCCard = $dbRes->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_EXTERNAL_ID, $arCCard, "sale_ps_handler_refund_edit.php?".$f_LINK_PARAMS."&handler=".ToLower($f_HANDLER)."&lang=".LANG);

	$row->AddField("EXTERNAL_ID", "<a href=\"sale_ps_handler_refund_edit.php?".$f_LINK_PARAMS.'&handler='.ToLower($f_HANDLER)."&lang=".LANG."\">".$f_EXTERNAL_ID."</a>");
	$row->AddField("NAME", htmlspecialcharsbx($f_NAME));
	$row->AddField("CONFIGURED", GetMessage("SALE_REFUND_HANDLERS_LIST_CONFIGURED_".$f_CONFIGURED));
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
