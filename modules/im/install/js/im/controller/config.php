<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/im/controller/dist/registry.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'im.tools.timer',
		'im.const',
	],
	'skip_core' => true,
];