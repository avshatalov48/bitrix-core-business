<?php

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$isNetworkProviderAvailable = static function() {
	$modulesIncluded = Loader::includeModule('imopenlines') && Loader::includeModule('imbot');
	$networkProviderExists = class_exists(\Bitrix\ImBot\Integration\Ui\EntitySelector\NetworkProvider::class);

	return $modulesIncluded && $networkProviderExists;
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
	'css' => 'dist/search.bundle.css',
	'js' => 'dist/search.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'im.v2.lib.old-chat-embedding.menu',
		'ui.fonts.opensans',
		'im.v2.lib.logger',
		'ui.dexie',
		'im.v2.lib.utils',
		'im.v2.component.old-chat-embedding.elements',
		'main.core',
		'main.core.events',
		'im.v2.const',
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