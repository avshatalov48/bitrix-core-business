<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/roomsmanager.bundle.css',
	'js' => 'dist/roomsmanager.bundle.js',
	'rel' => [
		'main.core',
		'calendar.util',
		'calendar.sectionmanager',
		'main.core.events',
	],
	'skip_core' => false,
];