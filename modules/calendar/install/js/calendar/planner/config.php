<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => [
		'/bitrix/js/calendar/planner.css'
	],
	'js' => 'dist/planner.bundle.js',
	'rel' => [
		'main.core.events',
		'calendar.util',
		'main.core',
		'calendar.ui.tools.draganddrop',
		'main.popup',
	],
	'skip_core' => false,
	'lang' => '/bitrix/modules/calendar/classes/general/calendar_planner.php'
];