<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/im/model/dist/registry.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'ui.vue.vuex',
		'im.const',
	],
	'skip_core' => true,
];