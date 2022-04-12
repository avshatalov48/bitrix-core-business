<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' =>[
		'./dist/audioplayer.bundle.js',
	],
	'css' => [
		'./dist/audioplayer.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'main.polyfill.intersectionobserver',
		'ui.vue3',
		'main.core.events',
	],
	'skip_core' => true,
];