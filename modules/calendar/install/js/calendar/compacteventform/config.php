<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => [
		'dist/compacteventform.bundle.css',
		'/bitrix/components/bitrix/calendar.grid/templates/.default/style.css',
	],
	'js' => 'dist/compacteventform.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'calendar.util',
		'main.popup',
		'calendar.controls',
		'calendar.entry',
		'calendar.sectionmanager',
		'ui.analytics',
		'ui.dialogs.messagebox',
		'calendar.entityrelation',
	],
	'skip_core' => false,
	'lang' => BX_ROOT.'/modules/calendar/classes/general/calendar_js.php',
];
