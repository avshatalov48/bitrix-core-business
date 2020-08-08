<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/controls.bundle.css',
	'js' => 'dist/controls.bundle.js',
	'rel' => [
		'main.popup',
		'calendar.util',
		'main.core',
		'main.core.events',
	],
	'skip_core' => false,
	'lang' => '/bitrix/modules/calendar/classes/general/editeventform_js.php'
];