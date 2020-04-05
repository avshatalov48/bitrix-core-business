<?
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/prolog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/prolog_admin_mobile_after.php');

$arParams = array(
	"ORDERS_LIST_PATH" => '/bitrix/admin/mobile/sale_orders_list.php'
	);

$APPLICATION->IncludeComponent(
	'bitrix:sale.mobile.order.detail',
	'.default',
	$arParams,
	false
);

require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/epilog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/epilog_admin_mobile_after.php');
?>