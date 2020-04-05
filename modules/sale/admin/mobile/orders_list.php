<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/prolog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/prolog_admin_mobile_after.php');

$arParams = array(
	"ORDER_DETAIL_PATH" => '/bitrix/admin/mobile/sale_order_detail.php'

	);

$APPLICATION->IncludeComponent(
	'bitrix:sale.mobile.orders.list',
	'.default',
	$arParams,
	false
);

require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/epilog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/epilog_admin_mobile_after.php');