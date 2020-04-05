<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/prolog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/prolog_admin_mobile_after.php');

$params = array(
	"DETAIL_URL" => "/bitrix/admin/mobile/bitrixcloud_monitoring_detail.php",
	"EDIT_URL" => "/bitrix/admin/mobile/bitrixcloud_monitoring_edit.php"
);

$APPLICATION->IncludeComponent(
	'bitrix:bitrixcloud.mobile.monitoring.list',
	'sites_list',
	$params,
	false
);

require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/epilog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mobileapp/include/epilog_admin_mobile_after.php');