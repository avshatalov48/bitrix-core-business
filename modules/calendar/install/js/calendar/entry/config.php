<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/entry.bundle.css',
	'js' => 'dist/entry.bundle.js',
	'rel' => [
		'calendar.entry',
		'calendar.calendarsection',
		'calendar.util',
		'main.core.events',
		'calendar.controls',
		'calendar.compacteventform',
		'main.core',
	],
	'skip_core' => false,
];