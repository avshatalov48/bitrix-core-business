<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/quotepanel.bundle.js',
	],
	'css' => [
		'./dist/quotepanel.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
	],
	'skip_core' => true,
];