<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/compacteventform.bundle.css',
	'js' => 'dist/compacteventform.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'calendar.util',
		'main.popup',
		'calendar.controls',
		'calendar.entry',
		'calendar.sectionmanager',
		'ui.dialogs.messagebox',
	],
	'skip_core' => false,
];