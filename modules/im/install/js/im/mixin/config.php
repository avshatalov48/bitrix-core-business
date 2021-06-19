<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue.vuex',
		'ui.vue',
		'im.lib.timer',
		'im.lib.clipboard',
		'im.lib.utils',
		'main.core.events',
		'im.const',
		'im.lib.uploader',
		'im.lib.logger',
	],
	'skip_core' => true,
];