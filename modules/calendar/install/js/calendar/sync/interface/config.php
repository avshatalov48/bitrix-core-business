<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/syncinterface.bundle.css',
	'js' => 'dist/syncinterface.bundle.js',
	'rel' => [
		'main.core.events',
		'ui.dialogs.messagebox',
		'calendar.util',
		'calendar.sync.manager',
		'ui.tilegrid',
		'ui.forms',
		'main.popup',
		'main.core',
	],
	'skip_core' => false,
];