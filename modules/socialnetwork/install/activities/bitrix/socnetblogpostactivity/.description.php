<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arActivityDescription = [
	'NAME' => Loc::getMessage('SNBPA_DESCR_NAME_2'),
	'DESCRIPTION' => Loc::getMessage('SNBPA_DESCR_DESCR_1'),
	'TYPE' => ['activity', 'robot_activity'],
	'CLASS' => 'SocnetBlogPostActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => [
		'ID' => "interaction",
	],
	'RETURN' => [
		"PostId" => [
			"NAME" => GetMessage("SNBPA_RETURN_POST_ID"),
			"TYPE" => "int",
		],
		"PostUrl" => [
			"NAME" => GetMessage("SNBPA_RETURN_POST_URL"),
			"TYPE" => "string",
		],
		"PostUrlBb" => [
			"NAME" => GetMessage("SNBPA_RETURN_POST_URL_BB"),
			"TYPE" => "string",
		],
	],
	'ROBOT_SETTINGS' => [
		'CATEGORY' => 'employee',
		'RESPONSIBLE_PROPERTY' => 'UsersTo',
		'GROUP' => ['informingEmployee'],
		'SORT' => 900,
	],
];