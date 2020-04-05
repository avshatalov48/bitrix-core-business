<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/im/component/element/attach/dist/attach.bundle.js',
	],
	'css' => [
		'/bitrix/js/im/component/element/attach/dist/attach.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'im.model',
		'im.utils',
		'ui.vue',
	],
	'skip_core' => true,
];