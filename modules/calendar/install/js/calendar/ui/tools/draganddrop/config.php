<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/draganddrop.bundle.css',
	'js' => 'dist/draganddrop.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'calendar.util',
	],
	'skip_core' => true,
];