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
		'ui.design-tokens',
		'ui.vue3',
		'pull.client',
		'main.core',
	],
	'skip_core' => false,
];