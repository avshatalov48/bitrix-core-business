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
		'im.v2.lib.logger',
		'main.core',
		'im.v2.const',
		'main.core.events',
	],
	'skip_core' => false,
	'settings' => [
		'isChatWindow' => defined('BX_DESKTOP') && BX_DESKTOP,
		'v2' => !\Bitrix\Im\Settings::isLegacyChatActivated(),
	]
];