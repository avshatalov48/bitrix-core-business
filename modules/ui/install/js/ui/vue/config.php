<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/ui/vue/dist/vue.bitrix.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue.vendor.v2',
	],
	'skip_core' => true,
];