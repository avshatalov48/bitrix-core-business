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
		'main.core.events',
		'main.core',
		'calendar.util',
		'ui.entity-selector',
		'ui.dialogs.messagebox',
	],
	'skip_core' => false,
];