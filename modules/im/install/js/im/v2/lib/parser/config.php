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
	'js' => './dist/parser.bundle.js',
	'css' => './dist/parser.bundle.css',
	'rel' => [
		'im.v2.lib.desktop-api',
		'main.core.events',
		'im.public',
		'main.core',
	],
	'skip_core' => false,
	'settings' => ['v2' => !\Bitrix\Im\Settings::isLegacyChatActivated()]
];