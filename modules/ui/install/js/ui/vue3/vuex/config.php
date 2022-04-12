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
		'ui.dexie',
		'main.md5',
		'main.core',
		'ui.vue3',
	],
	'skip_core' => false,
];