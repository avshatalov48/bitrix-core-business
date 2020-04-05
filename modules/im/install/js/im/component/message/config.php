<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/im/component/message/dist/message.bundle.js',
	],
	'css' => [
		'/bitrix/js/im/component/message/dist/message.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'im.component.message.body',
		'im.model',
		'ui.vue',
	],
	'skip_core' => true,
];