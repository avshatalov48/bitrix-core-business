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
		'ui.vue',
		'im.const',
		'im.lib.utils',
		'ui.vue.vuex',
	],
	'skip_core' => true,
];