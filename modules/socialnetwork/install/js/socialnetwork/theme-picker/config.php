<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/theme-picker.bundle.css',
	'js' => 'dist/theme-picker.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];