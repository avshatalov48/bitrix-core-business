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
		'im.lib.logger',
		'main.core.events',
		'im.lib.utils',
		'ui.vue',
		'ui.vue.vuex',
		'main.core',
		'im.const',
	],
	'skip_core' => false,
];