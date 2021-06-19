<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/controller.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'pull.client',
		'rest.client',
		'ui.vue.vuex',
		'im.model',
		'im.provider.pull',
		'im.provider.rest',
		'im.lib.timer',
		'im.const',
		'im.lib.utils',
		'ui.vue',
		'im.lib.logger',
	],
	'skip_core' => true,
];
