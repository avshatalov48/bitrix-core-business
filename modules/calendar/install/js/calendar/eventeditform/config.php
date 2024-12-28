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
	'js' => 'dist/eventeditform.bundle.js',
	'rel' => [
		'calendar.entry',
		'calendar.planner',
		'calendar.roomsmanager',
		'calendar.sectionmanager',
		'main.core.events',
		'ui.entity-selector',
		'main.core',
		'calendar.controls',
		'calendar.util',
	],
	'skip_core' => false,
	'lang' => '/bitrix/modules/calendar/classes/general/editeventform_js.php'
];