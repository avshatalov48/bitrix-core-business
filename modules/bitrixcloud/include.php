<?php

if (!defined('CACHED_b_bitrixcloud_option'))
{
	define('CACHED_b_bitrixcloud_option', 36000);
}

CModule::AddAutoloadClasses('bitrixcloud', [
	'CBitrixCloudException' => 'classes/general/exception.php',
	'CBitrixCloudOption' => 'classes/general/option.php',
	'CBitrixCloudWebService' => 'classes/general/webservice.php',
	'CBitrixCloudBackupWebService' => 'classes/general/backup_webservice.php',
	'CBitrixCloudBackup' => 'classes/general/backup.php',
	'CBitrixCloudMonitoringWebService' => 'classes/general/monitoring_webservice.php',
	'CBitrixCloudMonitoring' => 'classes/general/monitoring.php',
	'CBitrixCloudMonitoringResult' => 'classes/general/monitoring_result.php',
	'CBitrixCloudMobile' => 'classes/general/mobile.php'
]);

if (CModule::IncludeModule('clouds'))
{
	CModule::AddAutoloadClasses('bitrixcloud', [
		'CBitrixCloudBackupBucket' => 'classes/general/backup_bucket.php',
	]);
}

CJSCore::RegisterExt('mobile_monitoring', [
	'js' => '/bitrix/js/bitrixcloud/mobile_monitoring.js',
	'lang' => '/bitrix/modules/bitrixcloud/lang/' . LANGUAGE_ID . '/js_mobile_monitoring.php'
]);
