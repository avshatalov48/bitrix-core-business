<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' =>[
		'./dist/socialvideo.bundle.js',
	],
	'css' => [
		'./dist/socialvideo.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'main.polyfill.intersectionobserver',
		'ui.vue3.directives.lazyload',
		'main.core.events',
	],
	'skip_core' => true,
];