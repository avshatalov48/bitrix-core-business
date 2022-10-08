<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/recent.bundle.css',
	'js' => 'dist/recent.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue.vuex',
		'ui.design-tokens',
		'im.lib.utils',
		'im.const',
		'ui.vue',
		'main.core.events',
	],
	'skip_core' => true,
];