<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/props-list.bundle.css',
	'js' => 'dist/props-list.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
	],
	'skip_core' => true,
];