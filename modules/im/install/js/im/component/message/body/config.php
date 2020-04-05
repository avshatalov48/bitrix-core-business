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
		'im.component.element.media',
		'im.component.element.attach',
		'im.component.element.keyboard',
		'im.component.element.chatteaser',
		'ui.vue.components.reaction',
		'ui.vue',
		'ui.vue.vuex',
		'im.model',
		'im.const',
		'im.utils',
	],
	'skip_core' => true,
];