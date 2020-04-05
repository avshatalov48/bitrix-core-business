<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/im/utils/dist/utils.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'im.const',
	],
	'skip_core' => true,
];