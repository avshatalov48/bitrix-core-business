<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/categorymanager.bundle.css',
	'js' => 'dist/categorymanager.bundle.js',
	'rel' => [
		'calendar.sectionmanager',
		'main.core',
		'calendar.util',
	],
	'skip_core' => false,
];