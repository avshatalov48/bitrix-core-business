<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/prolog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/prolog_admin_mobile_after.php');

$APPLICATION->IncludeComponent(
	'bitrix:sale.mobile.orders.push',
	'.default',
	array(),
	false
);

require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/epilog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/epilog_admin_mobile_after.php');