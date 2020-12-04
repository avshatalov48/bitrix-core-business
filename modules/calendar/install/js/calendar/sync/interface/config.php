<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/syncinterface.bundle.css',
	'js' => 'dist/syncinterface.bundle.js',
	'rel' => [
		'ui.dialogs.messagebox',
		'calendar.util',
		'main.core.events',
		'ui.tilegrid',
		'ui.forms',
		'main.popup',
		'main.core',
	],
	'skip_core' => false,
];