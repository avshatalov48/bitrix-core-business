<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/textarea.bundle.js',
	],
	'css' => [
		'./dist/textarea.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'im.lib.localstorage',
		'im.lib.utils',
	],
	'skip_core' => true,
];