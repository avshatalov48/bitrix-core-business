<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/rooms.bundle.css',
	'js' => 'dist/rooms.bundle.js',
	'rel' => [
		'calendar.controls',
		'calendar.sectioninterface',
		'main.core',
		'main.core.events',
		'ui.entity-selector',
		'calendar.util',
	],
	'skip_core' => false,
];