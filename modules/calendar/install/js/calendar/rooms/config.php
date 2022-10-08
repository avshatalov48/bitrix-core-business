<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/rooms.bundle.css',
	'js' => 'dist/rooms.bundle.js',
	'rel' => [
		'calendar.sectioninterface',
		'main.core.events',
		'calendar.controls',
		'main.core',
		'calendar.util',
		'ui.entity-selector',
	],
	'skip_core' => false,
];