<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$maxPlannerUsers = 0;
if (\Bitrix\Main\Loader::includeModule('calendar'))
{
	$maxPlannerUsers = \CCalendar::GetMaxPlannerUsers();
}

return [
	'css' => [
		'/bitrix/js/calendar/planner.css'
	],
	'js' => 'dist/planner.bundle.js',
	'rel' => [
		'ui.info-helper',
		'main.core.events',
		'calendar.util',
		'main.core',
		'calendar.ui.tools.draganddrop',
		'main.popup',
		'main.date',
		'ui.avatar',
	],
	'settings' => [
		'maxPlannerUsers' => $maxPlannerUsers,
	],
	'skip_core' => false,
	'lang' => '/bitrix/modules/calendar/classes/general/calendar_planner.php'
];
