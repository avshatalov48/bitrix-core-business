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
		'ui.fonts.opensans',
		'ui.design-tokens',
		'im.view.message',
		'im.lib.utils',
		'im.lib.animation',
		'im.lib.logger',
		'main.polyfill.intersectionobserver',
		'ui.vue',
		'im.const',
		'main.core',
		'main.core.events',
		'ui.vue.vuex',
	],
	'skip_core' => false,
];