<?php

if (!defined("CACHED_b_bitrixcloud_option"))
	define("CACHED_b_bitrixcloud_option", 36000);

CModule::AddAutoloadClasses("bitrixcloud", array(
	"CBitrixCloudException" => "classes/general/exception.php",
	"CBitrixCloudOption" => "classes/general/option.php",
	"CBitrixCloudWebService" => "classes/general/webservice.php",
	"CBitrixCloudCDNWebService" => "classes/general/cdn_webservice.php",
	"CBitrixCloudCDNConfig" => "classes/general/cdn_config.php",
	"CBitrixCloudCDN" => "classes/general/cdn.php",
	"CBitrixCloudCDNQuota" => "classes/general/cdn_quota.php",
	"CBitrixCloudCDNClasses" => "classes/general/cdn_class.php",
	"CBitrixCloudCDNClass" => "classes/general/cdn_class.php",
	"CBitrixCloudCDNServerGroups" => "classes/general/cdn_server.php",
	"CBitrixCloudCDNServerGroup" => "classes/general/cdn_server.php",
	"CBitrixCloudCDNLocations" => "classes/general/cdn_location.php",
	"CBitrixCloudCDNLocation" => "classes/general/cdn_location.php",
	"CBitrixCloudBackupWebService" => "classes/general/backup_webservice.php",
	"CBitrixCloudBackup" => "classes/general/backup.php",
	"CBitrixCloudMonitoringWebService" => "classes/general/monitoring_webservice.php",
	"CBitrixCloudMonitoring" =>  "classes/general/monitoring.php",
	"CBitrixCloudMonitoringResult" => "classes/general/monitoring_result.php",
	"CBitrixCloudMobile" => "classes/general/mobile.php"
));

if(CModule::IncludeModule('clouds'))
{
	CModule::AddAutoloadClasses("bitrixcloud", array(
		"CBitrixCloudBackupBucket" => "classes/general/backup_bucket.php",
	));
}

CJSCore::RegisterExt('mobile_monitoring', array(
	'js' => '/bitrix/js/bitrixcloud/mobile_monitoring.js',
	'lang' => '/bitrix/modules/bitrixcloud/lang/'.LANGUAGE_ID.'/js_mobile_monitoring.php'
));
