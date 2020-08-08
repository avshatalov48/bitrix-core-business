<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/calendarsection.bundle.css',
	'js' => 'dist/calendarsection.bundle.js',
	'rel' => [
		'calendar.entry',
		'calendar.util',
		'main.core',
	],
	'skip_core' => false,
];