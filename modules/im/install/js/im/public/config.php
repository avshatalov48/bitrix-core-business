<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$isV2Enabled = false;
if (\Bitrix\Main\Loader::includeModule('im'))
{
	$isV2Enabled = !\Bitrix\Im\Settings::isLegacyChatActivated();
}

return [
	'js' => [
		'./dist/public.bundle.js',
	],
	'rel' => [
		'main.core',
	],
	'settings' => ['v2enabled' => $isV2Enabled],
	'skip_core' => false,
];