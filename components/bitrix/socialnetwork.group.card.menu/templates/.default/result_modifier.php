<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\UserToGroupTable;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

Loc::loadMessages(__FILE__);

$arResult['PAGE_BODY_STYLES'] = [
	'edit' => 'social-group-create-body',
];

$arResult['MENU_ITEMS'] = [
	'main' => [
		'NAME' => Loc::getMessage('SONET_GROUP_CARD_MENU_ITEM_TITLE_MAIN'),
		'ACTIVE' => false,
		'CHILDREN' => [
			'card' => [
				'NAME' => ($arResult['IS_PROJECT'] ? Loc::getMessage('SONET_GROUP_CARD_MENU_ITEM_TITLE_MAIN_ABOUT_PROJECT') : Loc::getMessage('SONET_GROUP_CARD_MENU_ITEM_TITLE_MAIN_ABOUT_GROUP')),
				'ATTRIBUTES' => [
					'data-action' => 'card',
				],
				'ACTIVE' => ($arResult['TAB'] === 'card'),
			],
		]
	],
	'members' => [
		'NAME' => Loc::getMessage('SONET_GROUP_CARD_MENU_ITEM_TITLE_MEMBERS'),
		'ACTIVE' => false,
		'CHILDREN' => [
			'members-list' => [
				'NAME' => Loc::getMessage('SONET_GROUP_CARD_MENU_ITEM_TITLE_MEMBERS_LIST'),
				'ATTRIBUTES' => [
					'data-action' => 'members-list',
					'data-url' => $arResult['URLS']['members-list'],
				],
				'ACTIVE' => ($arResult['TAB'] === 'members-list'),
			]
		],
	],
];

if ($arResult['PERMISSIONS']['UserCanModifyGroup'])
{
	$arResult['MENU_ITEMS']['main']['CHILDREN']['edit'] = [
		'NAME' => Loc::getMessage('SONET_GROUP_CARD_MENU_ITEM_TITLE_MAIN_EDIT'),
		'ATTRIBUTES' => [
			'data-action' => 'edit',
			'data-body-style' => $arResult['PAGE_BODY_STYLES']['edit'],
		],
		'ACTIVE' => ($arResult['TAB'] === 'edit'),
	];

	$arResult['MENU_ITEMS']['main']['CHILDREN']['copy'] = [
		'NAME' => Loc::getMessage('SONET_GROUP_CARD_MENU_ITEM_TITLE_MAIN_COPY'),
		'ATTRIBUTES' => [
			'data-action' => 'copy',
		],
		'ACTIVE' => ($arResult['TAB'] === 'copy'),
	];
}

if ($arResult['canPickTheme'])
{
	$arResult['MENU_ITEMS']['main']['CHILDREN']['theme'] = [
		'NAME' => Loc::getMessage('SONET_GROUP_CARD_MENU_ITEM_TITLE_MAIN_THEME'),
		'ATTRIBUTES' => [
			'data-action' => 'theme',
		],
		'ACTIVE' => ($arResult['TAB'] === 'theme'),
	];
}

if ($arResult['PERMISSIONS']['UserCanModifyGroup'])
{
	$arResult['MENU_ITEMS']['main']['CHILDREN']['delete'] = [
		'NAME' => Loc::getMessage('SONET_GROUP_CARD_MENU_ITEM_TITLE_MAIN_DELETE'),
		'ATTRIBUTES' => [
			'data-action' => 'delete',
		],
		'ACTIVE' => ($arResult['TAB'] === 'delete'),
	];
}

if (
	$arResult['PERMISSIONS']['UserIsMember']
	&& !$arResult['PERMISSIONS']['UserIsAutoMember']
	&& $arResult['PERMISSIONS']['UserRole'] !== UserToGroupTable::ROLE_OWNER
)
{
	$arResult['MENU_ITEMS']['main']['CHILDREN']['leave'] = [
		'NAME' => Loc::getMessage('SONET_GROUP_CARD_MENU_ITEM_TITLE_MAIN_LEAVE'),
		'ATTRIBUTES' => [
			'data-action' => 'leave',
		],
		'ACTIVE' => ($arResult['TAB'] === 'leave'),
	];
}

if (
	(
		!$arResult['PERMISSIONS']['UserIsMember']
		|| (
			$arResult['PERMISSIONS']['UserRole'] === UserToGroupTable::ROLE_REQUEST
			&& $arResult['PERMISSIONS']['InitiatedByType'] === UserToGroupTable::INITIATED_BY_GROUP
		)
	)
	&& !$arResult['HideArchiveLinks']
)
{
	$arResult['MENU_ITEMS']['main']['CHILDREN']['join'] = [
		'NAME' => Loc::getMessage('SONET_GROUP_CARD_MENU_ITEM_TITLE_MAIN_JOIN'),
		'ATTRIBUTES' => [
			'data-action' => '',
			'data-url' => $arResult['URLS']['join'],
		],
		'ACTIVE' => ($arResult['TAB'] === 'join'),
	];
}

if ($arResult['PERMISSIONS']['UserCanInitiate'])
{
	$arResult['MENU_ITEMS']['members']['CHILDREN']['requests-out'] = [
		'NAME' => Loc::getMessage('SONET_GROUP_CARD_MENU_ITEM_TITLE_MEMBERS_REQUESTS_OUT'),
		'ATTRIBUTES' => [
			'data-action' => 'requests-out',
			'data-url' => $arResult['URLS']['requests-out'],
		],
		'ACTIVE' => ($arResult['TAB'] === 'requests-out'),
	];

	if ($arResult['PERMISSIONS']['UserCanProcessRequestsIn'])
	{
		$arResult['MENU_ITEMS']['members']['CHILDREN']['requests-in'] = [
			'NAME' => Loc::getMessage('SONET_GROUP_CARD_MENU_ITEM_TITLE_MEMBERS_REQUESTS_IN'),
			'ATTRIBUTES' => [
				'data-action' => 'requests-in',
				'data-url' => $arResult['URLS']['requests-in'],
			],
			'ACTIVE' => ($arResult['TAB'] === 'requests-in'),
		];
	}
}

if (
	$arResult['PERMISSIONS']['UserCanModifyGroup']
	&& !$arResult['HideArchiveLinks']
	&& \Bitrix\Socialnetwork\Helper\Workgroup::getEditFeaturesAvailability()
)
{
	$arResult['MENU_ITEMS']['features'] = [
		'NAME' => Loc::getMessage('SONET_GROUP_CARD_MENU_ITEM_TITLE_FEATURES'),
		'ATTRIBUTES' => [
			'data-action' => 'features',
			'data-url' => $arResult['URLS']['features'],
		],
		'ACTIVE' => ($arResult['TAB'] === 'features'),
	];
}
