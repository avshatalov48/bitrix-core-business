<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => [
		'/bitrix/js/calendar/cal-style.css',
		'/bitrix/js/calendar/new/calendar.css',
		'/bitrix/components/bitrix/calendar.grid/templates/.default/style.css',
	],
	'js' => 'dist/eventviewform.bundle.js',
	'rel' => [
		'calendar.util',
		'main.core',
		'calendar.entry',
		'calendar.controls',
		'main.core.events',
		'calendar.planner',
		'intranet.control-button',
	],
	'skip_core' => false,
	'lang' => '/bitrix/modules/calendar/classes/general/calendar_js.php'
];