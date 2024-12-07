<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/bitrixvue.bundle.js',
	],
	'rel' => [
		'main.core.events',
		'main.core',
		'rest.client',
		'pull.client',
		'ui.vue3',
	],
	'skip_core' => false,
];