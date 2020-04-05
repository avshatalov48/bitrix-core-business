<?
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/prolog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/prolog_admin_mobile_after.php');

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if ($saleModulePermissions <= "D")
{
	echo GetMessage("ACCESS_DENIED");
}
else
{

	IncludeModuleLangFile(__FILE__);

	// <editor-fold desc="--------- Server processing ---------">
	require($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/sale/admin/report_view_prepdata.php');
	// </editor-fold>

	if(isset($_REQUEST["rep_templ"]))
		$reportTemplate = $_REQUEST["rep_templ"];
	else
		$reportTemplate = "admin_mobile_encl";

	$APPLICATION->IncludeComponent(
		'bitrix:report.view',
		$reportTemplate,
		array(
			'REPORT_ID' => $arParams['REPORT_ID'],
			'TITLE' => $arParams['TITLE'],
			'PATH_TO_REPORT_LIST' => $arParams['PATH_TO_REPORT_LIST'],
			'PATH_TO_REPORT_CONSTRUCT' => $arParams['PATH_TO_REPORT_CONSTRUCT'],
			'PATH_TO_REPORT_VIEW' => $arParams['PATH_TO_REPORT_VIEW'],
			'OWNER_ID' => $arParams['OWNER_ID'],
			'REPORT_HELPER_CLASS' => $arParams['REPORT_HELPER_CLASS'],
			'REPORT_CURRENCY_LABEL_TEXT' => $arParams['REPORT_CURRENCY_LABEL_TEXT'],
			'REPORT_WEIGHT_UNITS_LABEL_TEXT' => $arParams['REPORT_WEIGHT_UNITS_LABEL_TEXT'],
			'ROWS_PER_PAGE' => $arParams['ROWS_PER_PAGE'],
			'NAV_TEMPLATE' => $arParams['NAV_TEMPLATE'],
			'F_SALE_SITE' => $arParams['F_SALE_SITE'],
			'F_SALE_PRODUCT' => $arParams['F_SALE_PRODUCT'],
			'USE_CHART' => $arParams['USE_CHART']
		),
		null,
		array('HIDE_ICONS' => 'Y')
	);
}

require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/epilog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/epilog_admin_mobile_after.php');
?>