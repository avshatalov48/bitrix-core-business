<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/im/provider/rest/dist/registry.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'im.const',
		'ui.vue.vuex',
	],
	'skip_core' => true,
];