<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/im/component/element/chatteaser/dist/chatteaser.bundle.js',
	],
	'css' => [
		'/bitrix/js/im/component/element/chatteaser/dist/chatteaser.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'im.utils',
	],
	'skip_core' => true,
];