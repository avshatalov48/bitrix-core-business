<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/im/component/dialog/dist/dialog.bundle.js',
	],
	'css' => [
		'/bitrix/js/im/component/dialog/dist/dialog.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'main.polyfill.intersectionobserver',
		'ui.vue',
		'im.component.message',
	],
	'skip_core' => true,
];