<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('IM_ACTIVITIES_ADD_MEMBER_TO_GROUP_CHAT_NAME'),
	'DESCRIPTION' => Loc::getMessage('IM_ACTIVITIES_ADD_MEMBER_TO_GROUP_CHAT_DESCRIPTION'),
	'TYPE' => ['activity', 'robot_activity'],
	'CLASS' => 'ImAddMemberToGroupChatActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => 'other'
	],
	'ROBOT_SETTINGS' => [
		'CATEGORY' => 'employee',
		'GROUP' => ['informingEmployee'],
		'SORT' => 950,
	],
];