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
		'main.core',
		'main.sidepanel',
		'main.core.events',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.logger',
		'im.v2.application.launch',
		'im.v2.lib.desktop',
		'im.v2.lib.layout',
		'im.v2.lib.call',
		'im.v2.lib.confirm',
		'ui.notification',
		'im.v2.lib.opener',
	],
	'skip_core' => false,
];
