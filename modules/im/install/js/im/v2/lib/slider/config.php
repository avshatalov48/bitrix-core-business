<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!\Bitrix\Main\Loader::includeModule('im'))
{
	return [];
}

return [
	'js' => [
		'./dist/slider.bundle.js',
	],
	'rel' => [
		'im.v2.lib.desktop-api',
		'main.core',
		'main.core.events',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.logger',
		'im.v2.application.launch',
		'im.v2.lib.call',
		'im.v2.lib.phone',
		'im.v2.lib.utils',
		'im.v2.lib.desktop',
		'im.v2.provider.service',
		'ui.notification',
	],
	'settings' => [
		'v2enabled' => \Bitrix\Im\Settings::isBetaActivated()
	],
	'skip_core' => false,
];