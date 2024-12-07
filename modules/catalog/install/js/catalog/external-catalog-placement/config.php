<?

use Bitrix\Main\Config\Option;
use Bitrix\Catalog\Store\EnableWizard\TariffChecker;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
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
		'is1cPlanRestricted' => TariffChecker::isOnecInventoryManagementRestricted(),
	],
];