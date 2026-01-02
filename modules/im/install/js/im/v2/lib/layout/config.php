<?php

use Bitrix\Im\V2\Service\Locator;
use Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Loader::includeModule('im'))
{
	return [];
}

return [
	'js' => [
		'./dist/layout.bundle.js',
	],
	'rel' => [
		'main.core',
		'main.core.events',
		'im.v2.application.core',
		'im.v2.lib.analytics',
		'im.v2.lib.local-storage',
		'im.v2.const',
		'im.v2.lib.logger',
		'im.v2.lib.channel',
		'im.v2.lib.access',
		'im.v2.lib.feature',
		'im.v2.lib.bulk-actions',
	],
	'skip_core' => false,
	'settings' => [
		'isQuickAccessHidden' => Locator::getMessenger()->getApplication()->shouldHideQuickAccess(),
	]
];
