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
		'ui.vue',
		'ui.vue.vuex',
		'im.lib.logger',
		'im.const',
		'im.lib.utils',
		'im.view.dialog',
		'im.view.quotepanel',
	],
	'skip_core' => true,
];