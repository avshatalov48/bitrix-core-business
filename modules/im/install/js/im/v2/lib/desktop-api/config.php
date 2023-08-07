<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/desktop-api.bundle.js',
	],
	'rel' => [
		'main.core.events',
		'im.v2.const',
		'main.core',
	],
	'skip_core' => false,
	'settings' => [
		'v2' => \Bitrix\Im\Settings::isBetaActivated()
	]
];