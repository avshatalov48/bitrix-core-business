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
		'main.polyfill.core',
		'ui.vue3.directives.hint',
	],
	'skip_core' => true,
];