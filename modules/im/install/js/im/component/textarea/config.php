<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/im/component/textarea/dist/textarea.bundle.js',
	],
	'css' => [
		'/bitrix/js/im/component/textarea/dist/textarea.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'im.tools.localstorage',
		'im.utils',
	],
	'skip_core' => true,
];