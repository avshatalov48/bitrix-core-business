<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/textarea.bundle.js',
	],
	'css' => [
		'./dist/textarea.bundle.css',
	],
	'rel' => [
		'ui.design-tokens',
		'ui.vue',
		'im.lib.localstorage',
		'im.lib.utils',
		'main.core',
		'ui.vue.vuex',
		'main.core.events',
		'im.const',
	],
	'skip_core' => false,
];