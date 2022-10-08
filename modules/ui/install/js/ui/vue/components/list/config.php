<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' =>[
		'./dist/list.bundle.js',
	],
	'css' => [
		'./dist/list.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.design-tokens',
		'ui.vue',
	],
	'skip_core' => true,
];