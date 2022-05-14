<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/search.bundle.css',
	'js' => 'dist/search.bundle.js',
	'rel' => [
		'main.core',
		'calendar.util',
		'main.core.events',
	],
	'skip_core' => false,
];