<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/im/component/quotepanel/dist/quotepanel.bundle.js',
	],
	'css' => [
		'/bitrix/js/im/component/quotepanel/dist/quotepanel.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
	],
	'skip_core' => true,
];