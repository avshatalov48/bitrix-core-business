<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('IM_ACTIVITIES_CREATE_GROUP_CHAT_NAME'),
	'DESCRIPTION' => Loc::getMessage('IM_ACTIVITIES_CREATE_GROUP_CHAT_DESCRIPTION'),
	'TYPE' => ['activity', 'robot_activity'],
	'CLASS' => 'ImCreateGroupChatActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'other'
	],
	'ROBOT_SETTINGS' => [
		'CATEGORY' => 'employee',
		'GROUP' => ['informingEmployee'],
		'SORT' => 925,
	],
	'RETURN' => [
		'ChatId' => [
			'NAME' => Loc::getMessage('IM_ACTIVITIES_CREATE_GROUP_CHAT_RETURN_ID'),
			'TYPE' => 'int',
		],
	],
];