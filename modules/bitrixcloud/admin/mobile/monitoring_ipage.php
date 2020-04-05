<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/prolog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/prolog_admin_mobile_after.php');

$APPLICATION->IncludeComponent(
	'bitrix:bitrixcloud.mobile.monitoring.list',
	'.default',
	array(
		"DETAIL_URL" => "/bitrix/admin/mobile/bitrixcloud_monitoring_detail.php",
		"LIST_URL" => "/bitrix/admin/mobile/bitrixcloud_monitoring_list.php"
		),
	false
);

require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/epilog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/epilog_admin_mobile_after.php');