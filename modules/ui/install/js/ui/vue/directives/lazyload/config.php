<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/ui/vue/directives/lazyload/dist/lazyload.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'main.polyfill.intersectionobserver',
	],
	'skip_core' => true,
];