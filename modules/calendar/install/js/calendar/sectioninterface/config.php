<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => [
		'css' => 'dist/sectioninterface.bundle.css',
		'/bitrix/components/bitrix/calendar.grid/templates/.default/style.css',
	],
	'js' => 'dist/sectioninterface.bundle.js',
	'rel' => [
		'calendar.entry',
		'calendar.controls',
		'main.core.events',
		'ui.entity-selector',
		'main.core',
		'calendar.util',
		'calendar.sectionmanager',
	],
	'skip_core' => false,
	'lang' => '/bitrix/modules/calendar/classes/general/calendar_js.php'
];