<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/ui/dexie/dist/dexie3.bundle.js',
	],
	'skip_core' => true,
	'rel' => [
		'main.polyfill.core',
	],
];