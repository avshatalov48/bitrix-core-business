<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/search.bundle.css',
	'js' => 'dist/search.bundle.js',
	'rel' => [
		'calendar.util',
		'main.core.events',
		'main.core',
		'main.popup',
	],
	'skip_core' => false,
];