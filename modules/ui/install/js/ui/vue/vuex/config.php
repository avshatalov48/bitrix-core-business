<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/ui/vue/vuex/dist/vuex.bitrix.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'ui.dexie',
		'main.md5',
		'ui.vuex',
	],
	'skip_core' => true,
];