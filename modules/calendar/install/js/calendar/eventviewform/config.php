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
		'calendar.controls',
		'calendar.planner',
		'intranet.control-button',
		'ui.vue3',
		'calendar.util',
		'calendar.entry',
		'main.core',
		'main.core.events',
		'calendar.sectionmanager',
	],
	'skip_core' => false,
	'lang' => '/bitrix/modules/calendar/classes/general/calendar_js.php'
];