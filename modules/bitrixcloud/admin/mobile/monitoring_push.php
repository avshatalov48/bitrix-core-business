<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/mobileapp/include/prolog_admin_mobile_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/mobileapp/include/prolog_admin_mobile_after.php';
/* @var CMain $APPLICATION */

$APPLICATION->IncludeComponent(
	'bitrix:bitrixcloud.mobile.monitoring.push',
	'.default',
	[],
	false
);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/mobileapp/include/epilog_admin_mobile_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/mobileapp/include/epilog_admin_mobile_after.php';
