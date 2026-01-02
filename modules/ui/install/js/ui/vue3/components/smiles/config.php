<?php

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
		'rest.client',
		'ui.dexie',
		'ui.vue3.directives.lazyload',
	],
	'skip_core' => true,
];
