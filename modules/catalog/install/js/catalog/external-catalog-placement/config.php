<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Catalog\Store\EnableWizard\TariffChecker;
use Bitrix\Main\Loader;

$restricted = false;
if (Loader::includeModule('catalog'))
{
	$restricted = TariffChecker::isOnecInventoryManagementRestricted();
}

return [
	'css' => 'dist/external-catalog-placement.bundle.css',
	'js' => 'dist/external-catalog-placement.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
	],
	'skip_core' => false,
	'settings' => [
		'is1cPlanRestricted' => $restricted,
	],
];
