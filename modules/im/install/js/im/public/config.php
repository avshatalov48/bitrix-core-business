<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\Loader::includeModule('im');

return [
	'js' => [
		'./dist/public.bundle.js',
	],
	'rel' => [
		'main.core',
	],
	'settings' => [
		'v2enabled' => \Bitrix\Im\Settings::isBetaActivated()
	],
	'skip_core' => false,
];
