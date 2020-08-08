<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/dialog.bundle.js',
	],
	'css' => [
		'./dist/dialog.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'main.polyfill.intersectionobserver',
		'ui.vue',
		'ui.vue.vuex',
		'im.view.message',
		'im.const',
		'im.lib.utils',
		'im.lib.animation',
		'im.lib.logger',
	],
	'skip_core' => true,
];