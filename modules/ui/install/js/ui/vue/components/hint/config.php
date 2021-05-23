<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/hint.bundle.js',
	],
	'rel' => [
		'main.core',
		'main.popup',
		'ui.hint',
		'ui.vue',
	],
	'skip_core' => false,
];