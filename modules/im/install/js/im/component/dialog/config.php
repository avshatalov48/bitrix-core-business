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
		'im.view.message',
		'im.mixin',
		'im.lib.utils',
		'im.lib.animation',
		'im.lib.logger',
		'main.polyfill.intersectionobserver',
		'ui.vue.vuex',
		'ui.vue',
		'im.const',
		'main.core',
		'main.core.events',
	],
	'skip_core' => false,
];