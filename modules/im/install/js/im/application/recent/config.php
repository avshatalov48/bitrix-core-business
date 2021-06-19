<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/recent.bundle.js',
	],
	'css' => [
		'./dist/recent.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'im.application.core',
		'ui.vue',
		'im.component.recent',
	],
	'skip_core' => true,
];