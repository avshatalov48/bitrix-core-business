<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$isBeta = false;
if (\Bitrix\Main\Loader::includeModule('im'))
{
	$isBeta = \Bitrix\Im\Settings::isBetaActivated();
}

return [
	'js' => [
		'./dist/public.bundle.js',
	],
	'rel' => [
		'main.core',
	],
	'settings' => [
		'v2enabled' => $isBeta
	],
	'skip_core' => false,
];
