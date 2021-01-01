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
		'main.date',
		'main.popup',
		'main.core.events',
	],
	'lang' => BX_ROOT.'/modules/calendar/lib/userfield/resourcebooking.php',
	'skip_core' => false,
];