<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/message.bundle.js',
	],
	'css' => [
		'./dist/message.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'im.view.message.body',
		'im.model',
		'ui.vue',
		'im.const',
		'im.lib.utils',
		'im.lib.animation',
		'main.core.events',
	],
	'skip_core' => true,
];