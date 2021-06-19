<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' =>[
		'/bitrix/js/ui/vue/components/socialvideo/dist/socialvideo.bundle.js',
	],
	'css' => [
		'/bitrix/js/ui/vue/components/socialvideo/dist/socialvideo.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue.directives.lazyload',
		'main.polyfill.intersectionobserver',
		'ui.vue',
		'main.core.events',
	],
	'skip_core' => true,
];