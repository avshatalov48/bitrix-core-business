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
		'main.loader',
		'calendar.entry',
		'ui.dialogs.messagebox',
		'ui.buttons',
		'calendar.planner',
		'ui.entity-selector',
		'main.core.events',
		'main.popup',
		'calendar.controls',
		'intranet.control-button',
		'main.core',
		'calendar.util',
	],
	'skip_core' => false,
	'lang' => '/bitrix/modules/calendar/classes/general/editeventform_js.php'
];