<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/address.bundle.css',
	'js' => 'dist/address.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'location.core',
		'location.widget',
	],
	'skip_core' => true,
];