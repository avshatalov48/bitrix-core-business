<?php

use Bitrix\Main\Loader;
use Bitrix\UI\FeaturePromoter;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$isCloud = Loader::includeModule('bitrix24');
$isCP = Loader::includeModule('intranet');

return [
	'css' => 'dist/info-helper.bundle.css',
	'js' => 'dist/info-helper.bundle.js',
	'rel' => [
		'main.loader',
		'ui.info-helper',
		'ui.popup-with-header',
		'ui.analytics',
		'main.core',
	],
	'skip_core' => false,
	'settings' => [
		'popupProviderEnabled' => (new FeaturePromoter\PopupProviderAvailabilityChecker())->isAvailable(),
		'licenseType' => $isCloud ? strtoupper(\CBitrix24::getLicenseType()) : null,
		'region' => \Bitrix\Main\Application::getInstance()->getLicense()->getRegion() ?? 'en',
		'licenseNeverPayed' => $isCloud && \CBitrix24::isLicenseNeverPayed(),
		'marketUrl' => $isCP ? \Bitrix\Intranet\Binding\Marketplace::getMainDirectory() : false,
		'settingsUrl' => $isCP ? \Bitrix\Intranet\PortalSettings::getInstance()->getSettingsUrl() : '/settings/configs/',
		'isUpgradeTariffAvailable' => $isCloud && \CBitrix24::getPromoLicense(),
	],
];