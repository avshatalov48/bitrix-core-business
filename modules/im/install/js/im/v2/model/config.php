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
		'ui.vue3',
		'im.v2.lib.logger',
		'main.core.events',
		'main.core',
		'ui.vue3.vuex',
		'im.v2.const',
		'im.v2.lib.utils',
	],
	'skip_core' => false,
];