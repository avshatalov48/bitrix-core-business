<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => [
		'css' => 'dist/settingsinterface.bundle.css',
		'/bitrix/components/bitrix/calendar.grid/templates/.default/style.css',
		'/bitrix/js/calendar/new/calendar.css',
	],
	'js' => 'dist/settingsinterface.bundle.js',
	'rel' => [
		'calendar.util',
		'calendar.controls',
		'main.core',
		'ui.entity-selector',
		'main.core.events',
		'ui.info-helper',
		'ui.messagecard',
	],
	'skip_core' => false,
	'lang' => '/bitrix/modules/calendar/classes/general/calendar_js.php',
];