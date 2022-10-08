<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/controls.bundle.css',
	'js' => 'dist/controls.bundle.js',
	'rel' => [
		'calendar.roomsmanager',
		'calendar.categorymanager',
		'ui.icons.b24',
		'calendar.entry',
		'calendar.planner',
		'ui.entity-selector',
		'intranet.control-button',
		'main.core.events',
		'calendar.util',
		'main.core',
		'main.popup',
		'calendar.controls',
	],
	'skip_core' => false,
	'lang' => '/bitrix/modules/calendar/classes/general/editeventform_js.php'
];