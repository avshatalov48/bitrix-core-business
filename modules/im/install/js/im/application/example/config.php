<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/example.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'im.application.core',
		'ui.vue',
		'im.lib.logger',
	],
	'skip_core' => true,
];