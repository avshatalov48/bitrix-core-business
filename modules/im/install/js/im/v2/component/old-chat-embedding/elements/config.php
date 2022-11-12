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
		'im.v2.lib.utils',
		'ui.fonts.opensans',
		'im.v2.const',
		'ui.vue3',
	],
	'skip_core' => true,
];