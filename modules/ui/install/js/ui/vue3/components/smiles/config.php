<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' =>[
		'./dist/smiles.bundle.js',
	],
	'css' => [
		'./dist/smiles.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue3.directives.lazyload',
		'ui.vue3',
		'ui.dexie',
		'rest.client',
	],
	'skip_core' => true,
];