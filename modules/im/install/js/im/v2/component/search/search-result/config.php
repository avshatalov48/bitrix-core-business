<?php

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$isNetworkProviderAvailable = static function() {
	$modulesIncluded = Loader::includeModule('imopenlines') && Loader::includeModule('imbot');
	$modulesInstalled = ModuleManager::isModuleInstalled('imopenlines') && ModuleManager::isModuleInstalled('imbot');
	$networkProviderExists = class_exists(\Bitrix\ImBot\Integration\Ui\EntitySelector\NetworkProvider::class);

	return $modulesIncluded && $modulesInstalled && $networkProviderExists;
};

$isNetworkSearchEnabled = static function() {
	$modulesIncluded = Loader::includeModule('imconnector');
	if (!$modulesIncluded)
	{
		return false;
	}

	$optionMethodExists = method_exists('\Bitrix\ImConnector\Connectors\Network', 'isSearchEnabled');
	if (!$optionMethodExists)
	{
		return true;
	}

	return \Bitrix\ImConnector\Connectors\Network::isSearchEnabled();
};

return [
	'css' => 'dist/search-result.bundle.css',
	'js' => 'dist/search-result.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'ui.fonts.opensans',
		'ui.vue3',
		'im.public',
		'im.v2.lib.utils',
		'im.v2.lib.user',
		'im.v2.application.core',
		'ui.dexie',
		'im.v2.lib.rest',
		'im.v2.lib.logger',
		'im.v2.lib.menu',
		'main.core',
		'im.v2.component.animation',
		'main.core.events',
		'im.v2.const',
		'im.v2.component.elements',
	],
	'settings' => [
		'minTokenSize' => \Bitrix\Main\ORM\Query\Filter\Helper::getMinTokenSize(),
		'isNetworkAvailable' => $isNetworkProviderAvailable(),
		'isNetworkSearchEnabled' => $isNetworkSearchEnabled(),
		'isDepartmentsAvailable' => Loader::includeModule('intranet') && ModuleManager::isModuleInstalled('intranet'),
		'isCrmAvailable' => Loader::includeModule('crm') && ModuleManager::isModuleInstalled('crm'),
	],
	'skip_core' => false,
];