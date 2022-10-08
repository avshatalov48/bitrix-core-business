<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' =>[
		'./dist/status.bundle.js',
	],
	'css' => [
		'./dist/status.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.design-tokens',
		'ui.vue',
		'pull.client',
	],
	'skip_core' => true,
];