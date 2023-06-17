<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\Loader::includeModule('im');

$pathToUserCalendar = \Bitrix\Main\Config\Option::get(
	'calendar',
	'path_to_user_calendar',
	'/company/personal/user/#user_id#/calendar/'
);

return [
	'js' => './dist/utils.bundle.js',
	'rel' => [
		'im.v2.lib.desktop',
		'main.date',
		'im.v2.lib.date-formatter',
		'im.v2.const',
		'main.core',
	],
	'skip_core' => false,
	'settings' => [
		'limitOnline' => \CUser::GetSecondsForLimitOnline(),
		'pathToUserCalendar' => $pathToUserCalendar,
	]
];