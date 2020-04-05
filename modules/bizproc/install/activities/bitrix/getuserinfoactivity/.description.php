<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = [
	"NAME" => GetMessage("BPGUIA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPGUIA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "GetUserInfoActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => [
		"ID" => "other",
	],
	"RETURN" => [
		'USER_ACTIVE' => [
			'NAME' => GetMessage('BPGUIA_USER_ACTIVE'),
			'TYPE' => 'bool',
		],
		'USER_EMAIL' => [
			'NAME' => GetMessage('BPGUIA_USER_EMAIL'),
			'TYPE' => 'string',
		],
		'USER_WORK_PHONE' => [
			'NAME' => GetMessage('BPGUIA_USER_WORK_PHONE'),
			'TYPE' => 'string',
		],
		'USER_PERSONAL_MOBILE' => [
			'NAME' => GetMessage('BPGUIA_USER_PERSONAL_MOBILE'),
			'TYPE' => 'string',
		],
		'USER_UF_PHONE_INNER' => [
			'NAME' => GetMessage('BPGUIA_USER_UF_PHONE_INNER'),
			'TYPE' => 'string',
		],
		'USER_LOGIN' => [
			'NAME' => GetMessage('BPGUIA_USER_LOGIN'),
			'TYPE' => 'string',
		],
		'USER_LAST_NAME' => [
			'NAME' => GetMessage('BPGUIA_USER_LAST_NAME'),
			'TYPE' => 'string',
		],
		'USER_NAME' => [
			'NAME' => GetMessage('BPGUIA_USER_NAME'),
			'TYPE' => 'string',
		],
		'USER_SECOND_NAME' => [
			'NAME' => GetMessage('BPGUIA_USER_SECOND_NAME'),
			'TYPE' => 'string',
		],
		'USER_WORK_POSITION' => [
			'NAME' => GetMessage('BPGUIA_USER_WORK_POSITION'),
			'TYPE' => 'string',
		],
		'USER_PERSONAL_WWW' => [
			'NAME' => GetMessage('BPGUIA_USER_PERSONAL_WWW'),
			'TYPE' => 'string',
		],
		'USER_PERSONAL_CITY' => [
			'NAME' => GetMessage('BPGUIA_USER_PERSONAL_CITY'),
			'TYPE' => 'string',
		],
		'USER_UF_SKYPE' => [
			'NAME' => GetMessage('BPGUIA_USER_UF_SKYPE'),
			'TYPE' => 'string',
		],
		'USER_UF_TWITTER' => [
			'NAME' => GetMessage('BPGUIA_USER_UF_TWITTER'),
			'TYPE' => 'string',
		],
		'USER_UF_FACEBOOK' => [
			'NAME' => GetMessage('BPGUIA_USER_UF_FACEBOOK'),
			'TYPE' => 'string',
		],
		'USER_UF_LINKEDIN' => [
			'NAME' => GetMessage('BPGUIA_USER_UF_LINKEDIN'),
			'TYPE' => 'string',
		],
		'USER_UF_XING' => [
			'NAME' => GetMessage('BPGUIA_USER_UF_XING'),
			'TYPE' => 'string',
		],
		'USER_UF_WEB_SITES' => [
			'NAME' => GetMessage('BPGUIA_USER_UF_WEB_SITES'),
			'TYPE' => 'string',
		],
		'USER_UF_DEPARTMENT' => [
			'NAME' => GetMessage('BPGUIA_USER_UF_DEPARTMENT'),
			'TYPE' => 'int',
		],
		'IS_ABSENT' => [
			'NAME' => GetMessage('BPGUIA_IS_ABSENT'),
			'TYPE' => 'bool',
		],
		'TIMEMAN_STATUS' => [
			'NAME' => GetMessage('BPGUIA_TIMEMAN_STATUS'),
			'TYPE' => 'string',
		],
	],
];