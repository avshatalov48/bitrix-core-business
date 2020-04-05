<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/im/component/message/body/dist/body.bundle.js',
	],
	'css' => [
		'/bitrix/js/im/component/message/body/dist/body.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'im.model',
		'im.component.element.file',
	],
	'skip_core' => true,
];