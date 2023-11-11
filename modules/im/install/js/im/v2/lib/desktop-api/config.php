<?
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
		'./dist/desktop-api.bundle.js',
	],
	'rel' => [
		'main.core.events',
		'im.v2.const',
		'main.core',
		'im.v2.lib.logger',
	],
	'skip_core' => false,
	'settings' => [
		'v2' => \Bitrix\Im\Settings::isBetaActivated(),
		'isChatWindow' => defined('BX_DESKTOP') && BX_DESKTOP,
	]
];