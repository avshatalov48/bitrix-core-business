<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/info-helper.bundle.css',
	'js' => 'dist/info-helper.bundle.js',
	'rel' => [
		'main.loader',
		'ui.popup-with-header',
		'ui.analytics',
		'main.core',
	],
	'skip_core' => false,
	'settings' => [
		'popupProviderEnabled' => Option::get('ui', 'info-helper-popup-provider', 'N') === 'Y',
		'licenseType' => Loader::includeModule('bitrix24') ? strtoupper(\CBitrix24::getLicenseType()) : null,
	],
];