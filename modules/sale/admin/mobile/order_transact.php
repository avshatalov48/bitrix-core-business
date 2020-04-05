<?
/**
 * Shop mobile admin order's transactions //develop
 */

require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/prolog_admin_mobile.php');

$APPLICATION->IncludeComponent(
	'bitrix:sale.mobile.order.transact',
	'.default',
	array(),
	false
);

require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/epilog_admin_mobile.php');
?>