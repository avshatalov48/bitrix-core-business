<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/chatteaser.bundle.js',
	],
	'css' => [
		'./dist/chatteaser.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.design-tokens',
		'ui.vue',
		'im.lib.utils',
	],
	'skip_core' => true,
];