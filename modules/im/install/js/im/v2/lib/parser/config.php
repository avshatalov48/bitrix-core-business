<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\Loader::includeModule('im');

return [
	'js' => './dist/parser.bundle.js',
	'css' => './dist/parser.bundle.css',
	'rel' => [
		'main.core.events',
		'main.core',
	],
	'skip_core' => false,
	'settings' => [
		'v2' => \Bitrix\Im\Settings::isBetaActivated()
	]
];