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
		'im.const',
		'ui.vue',
		'ui.vue.vuex',
		'im.lib.utils',
		'main.core',
	],
	'skip_core' => false,
];