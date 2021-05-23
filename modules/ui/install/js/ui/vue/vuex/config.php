<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/vuex.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'ui.dexie',
		'main.md5',
	],
	'skip_core' => true,
];