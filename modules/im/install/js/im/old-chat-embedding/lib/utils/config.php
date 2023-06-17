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
		'main.date',
		'im.old-chat-embedding.const',
		'main.core',
	],
	'skip_core' => false,
	'settings' => [
		'limitOnline' => \CUser::GetSecondsForLimitOnline(),
		'emoji' => \Bitrix\Im\Text::getEmojiList(),
		'pathToUserCalendar' => $pathToUserCalendar,
	]
];