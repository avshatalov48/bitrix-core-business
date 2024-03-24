<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @var CDatabase $DB */
/** @var CUser $USER */
/** @var CMain $APPLICATION */

if (!$USER->CanDoOperation('bitrixcloud_monitoring'))
{
	ShowError(GetMessage('BCLMME_ACCESS_DENIED'));
	return;
}

if (!CModule::IncludeModule('bitrixcloud'))
{
	ShowError(GetMessage('BCLMME_BC_NOT_INSTALLED'));
	return;
}

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError(GetMessage('BCLMME_MA_NOT_INSTALLED'));
	return;
}

$arResult = [
	'CURRENT_PAGE' => $APPLICATION->GetCurPage(),
	'AJAX_URL' => $componentPath . '/ajax.php',
	'DOMAIN' => $_REQUEST['domain'] ?? '',
	'DOMAINS_NAMES' => [],
	'OPTIONS' => []
];

$monitoring = CBitrixCloudMonitoring::getInstance();
$monitoringResults = $monitoring->getMonitoringResults();

if ($arResult['DOMAIN'] != '')
{
	$arUserDevices = CBitrixCloudMobile::getUserDevices($USER->GetID());
	$arMonDevices = $monitoring->getDevices($arResult['DOMAIN']);

	foreach ($arUserDevices as $deviceId)
	{
		if (in_array($deviceId, $arMonDevices, true))
		{
			$arResult['OPTIONS']['SUBSCRIBE'] = 'Y';
		}
		else
		{
			$arResult['OPTIONS']['SUBSCRIBE'] = 'N';
		}
	}
}
else
{
	foreach ($monitoringResults as $domainName => $tmp)
	{
		$arResult['DOMAINS_NAMES'][] = $domainName;
	}
}

CJSCore::Init('ajax');

$this->includeComponentTemplate();
