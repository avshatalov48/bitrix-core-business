<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/interface.bundle.css',
	'js' => 'dist/interface.bundle.js',
	'rel' => [
		'main.core.events',
		'calendar.util',
		'main.core',
		'ui.switcher',
		'spotlight',
	],
	'skip_core' => false,
];