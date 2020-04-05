<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/im/provider/pull/dist/registry.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'pull.client',
		'ui.vue.vuex',
	],
	'skip_core' => true,
];