<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/resourcebookinguserfield.bundle.css',
	'js' => 'dist/resourcebookinguserfield.bundle.js',
	'rel' => [
		'helper',
		'socnetlogdest',
		'main.core',
		'calendar.resourcebookinguserfield',
		'calendar.resourcebooking',
	],
	'lang' => BX_ROOT.'/modules/calendar/lib/userfield/resourcebooking.php',
	'skip_core' => false,
];