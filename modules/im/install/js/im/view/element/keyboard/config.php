<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/keyboard.bundle.js',
	],
	'css' => [
		'./dist/keyboard.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.design-tokens',
		'ui.vue',
		'im.lib.utils',
		'im.lib.logger',
	],
	'skip_core' => true,
];