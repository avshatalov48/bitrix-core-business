<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/sectionmanager.bundle.css',
	'js' => 'dist/sectionmanager.bundle.js',
	'rel' => [
		'calendar.util',
		'calendar.sectionmanager',
		'main.core',
		'main.core.events',
	],
	'skip_core' => false,
];