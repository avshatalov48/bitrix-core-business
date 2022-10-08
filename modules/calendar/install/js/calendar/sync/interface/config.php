<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/syncinterface.bundle.css',
	'js' => 'dist/syncinterface.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'ui.fonts.opensans',
		'calendar.sync.manager',
		'calendar.entry',
		'ui.tilegrid',
		'ui.forms',
		'main.core.events',
		'ui.dialogs.messagebox',
		'main.core',
		'calendar.util',
		'main.popup',
	],
	'skip_core' => false,
];