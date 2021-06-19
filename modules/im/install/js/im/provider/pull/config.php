<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/registry.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue.vuex',
		'im.lib.logger',
		'main.core.events',
		'im.const',
		'pull.client',
	],
	'skip_core' => true,
];