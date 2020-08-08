<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/resourcebooking.bundle.css',
	'js' => 'dist/resourcebooking.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'main.date',
		'main.popup',
	],
	'lang' => BX_ROOT.'/modules/calendar/lib/userfield/resourcebooking.php',
	'skip_core' => false,
];