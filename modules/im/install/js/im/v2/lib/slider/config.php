<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\Loader::includeModule('im');

return [
	'js' => [
		'./dist/slider.bundle.js',
	],
	'rel' => [
		'main.core',
		'main.core.events',
		'im.v2.const',
		'im.v2.lib.logger',
		'im.v2.application.launch',
		'im.v2.lib.call',
		'im.v2.lib.utils',
		'ui.notification',
	],
	'settings' => [
		'v2enabled' => \Bitrix\Im\Settings::isBetaActivated()
	],
	'skip_core' => false,
];