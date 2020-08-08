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
		'ui.vue.vuex',
		'im.view.list.recent',
		'im.lib.logger',
		'im.const',
		'im.lib.utils',
	],
	'skip_core' => true,
];