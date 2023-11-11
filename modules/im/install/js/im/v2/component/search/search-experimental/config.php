<?php

use Bitrix\ImConnector\Connectors\Network;
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

	$optionMethodExists = method_exists(Network::class, 'isSearchEnabled');
	if (!$optionMethodExists)
	{
		return true;
	}

	return \Bitrix\ImConnector\Connectors\Network::isSearchEnabled();
};

return [
	'css' => 'dist/search-experimental.bundle.css',
	'js' => 'dist/search-experimental.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'ui.fonts.opensans',
		'im.public',
		'im.v2.lib.slider',
		'im.v2.lib.utils',
		'im.v2.lib.logger',
		'im.v2.provider.service',
		'im.v2.lib.menu',
		'im.v2.application.core',
		'main.core.events',
		'main.core',
		'im.v2.const',
		'im.v2.lib.date-formatter',
		'im.v2.lib.text-highlighter',
		'im.v2.component.elements',
	],
	'settings' => [
		'minTokenSize' => \Bitrix\Main\ORM\Query\Filter\Helper::getMinTokenSize(),
		'isNetworkAvailable' => $isNetworkProviderAvailable(),
		'isNetworkSearchEnabled' => $isNetworkSearchEnabled(),
	],
	'skip_core' => false,
];