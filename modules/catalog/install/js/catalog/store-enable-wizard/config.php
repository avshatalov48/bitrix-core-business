<?

use Bitrix\Catalog\Store\EnableWizard\Manager;
use Bitrix\Catalog\Store\EnableWizard\ConditionsChecker;
use Bitrix\Catalog\Store\EnableWizard\ModeList;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Engine\CurrentUser;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/index.bundle.css',
	'js' => 'dist/index.bundle.js',
	'rel' => [
		'ui.icon-set.api.vue',
		'main.popup',
		'ui.hint',
		'ui.forms',
		'ui.buttons',
		'catalog.tool-availability-manager',
		'ui.icon-set.main',
		'ui.icon-set.crm',
		'ui.icon-set.actions',
		'ui.vue3',
		'main.core',
	],
	'skip_core' => false,
	'settings' => [
		'isWithOrdersMode' => Loader::includeModule('crm') && \CCrmSaleHelper::isWithOrdersMode(),
		'currentMode' => Manager::getCurrentMode(),
		'availableModes' => Manager::getAvailableModes(),
		'feedbackFormOtherVersion1CPresets' => [
			'fromDomain' =>
				defined('BX24_HOST_NAME')
					? BX24_HOST_NAME
					: Bitrix\Main\Config\Option::get('main', 'server_name', '')
			,
			'b24_plan' => Loader::includeModule('bitrix24') ? CBitrix24::getLicenseType() : '',
			'c_name' => CurrentUser::get()->getFullName(),
		],
	],
];
