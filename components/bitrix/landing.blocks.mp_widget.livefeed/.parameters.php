<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Socialnetwork\WorkgroupTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$publicationGroupId = Option::get('landing', 'mainpage_id_publication_group');
if (Loader::includeModule('socialnetwork') && $publicationGroupId > 0)
{
	$res = WorkgroupTable::getList([
		'filter' => [
			'@ID' => $publicationGroupId
		],
		'select' => [ 'ID', 'NAME' ],
		'limit' => 1,
	]);
	$groupRow = $res->fetch();
	$defaultSocialGroupName = $groupRow['NAME'];
	$defaultSocialGroupValue = 'SG' . $groupRow['ID'];
}

$groupIdDefaultFilter = [];
if (isset($defaultSocialGroupValue) && isset($defaultSocialGroupName))
{
	$groupIdDefaultFilter =  [
		'key' => 'GROUP_ID',
		'value' => $defaultSocialGroupValue,
		'name' => $defaultSocialGroupName,
	];
}

$arComponentParameters = [
	'PARAMETERS' => [
		'TITLE' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_LIVEFEED_TITLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => Loc::getMessage('LANDING_WIDGET_LIVEFEED_TITLE_DEFAULT_VALUE'),
		],
		'GROUP_ID' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_LIVEFEED_GROUP_ID'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initDynamicSource',
			'JS_DATA' => Json::encode([
				'sources' => ['socialnetwork:livefeed'],
				'title' => Loc::getMessage('LANDING_WIDGET_LIVEFEED_GROUP_ID'),
				'stubText' => Loc::getMessage('LANDING_WIDGET_LIVEFEED_STUB_TEXT'),
				'useLink' => true,
				'linkType' => 'group',
			]),
			'DEFAULT' => [
				'filter' => [
					$groupIdDefaultFilter
				],
				'source' => 'socialnetwork:livefeed',
			],
		],
		'COLOR_BUTTON' => [
			'NAME' => Loc::getMessage('LANDING_WIDGET_LIVEFEED_COLOR_BUTTON'),
			'TYPE' => 'CUSTOM',
			'JS_EVENT' => 'initColorField',
		],
	],
];

$parentComponentParameters = @\CComponentUtil::GetComponentProps(
	'bitrix:landing.blocks.mp_widget.base',
);
$arComponentParameters['PARAMETERS'] = array_merge(
	$parentComponentParameters['PARAMETERS'],
	$arComponentParameters['PARAMETERS']
);

$arComponentParameters['PARAMETERS']['COLOR_BUTTON']['DEFAULT'] = '#bdc1c6';
$arComponentParameters['PARAMETERS']['COLOR_HEADERS']['DEFAULT'] = '#333333';
$arComponentParameters['PARAMETERS']['COLOR_HEADERS_V2']['DEFAULT'] = '#ffffff';