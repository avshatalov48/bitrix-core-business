<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Lists\Copy\Integration\Group;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$pageId = 'group_group_lists';

include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/bitrix/socialnetwork_group/templates/.default/util_group_menu.php');
include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/bitrix/socialnetwork_group/templates/.default/util_group_profile.php');

$iblockTypeId = Option::get('lists', 'socnet_iblock_type_id');

$navChainComponentParams = [
	'IBLOCK_TYPE_ID' => $iblockTypeId,
	'SOCNET_GROUP_ID' => $arResult['VARIABLES']['group_id'],
	'ADD_NAVCHAIN_GROUP' => 'Y',
	'PATH_TO_GROUP' => $arResult['PATH_TO_GROUP'],
	'LISTS_URL' => $arResult['PATH_TO_GROUP_LISTS'],
	'ADD_NAVCHAIN_LIST' => 'N',
	'ADD_NAVCHAIN_SECTIONS' => 'N',
	'ADD_NAVCHAIN_ELEMENT' => 'N',
];

$copyCheckerComponentParams = [
	'moduleId' => Group::MODULE_ID,
	'queueId' => $arResult['VARIABLES']['group_id'],
	'stepperClassName' => Group::STEPPER_CLASS,
	'checkerOption' => Group::CHECKER_OPTION,
	'errorOption' => Group::ERROR_OPTION,
	'titleMessage' => Loc::getMessage('LISTS_STEPPER_PROGRESS_TITLE'),
	'errorMessage' => Loc::getMessage('LISTS_STEPPER_PROGRESS_ERROR'),
];

$listsComponentParams = [
	'IBLOCK_TYPE_ID' => $iblockTypeId,
	'LISTS_URL' => $arResult['PATH_TO_GROUP_LISTS'],
	'LIST_URL' => $arResult['PATH_TO_GROUP_LIST_VIEW'],
	'LIST_EDIT_URL' => $arResult['PATH_TO_GROUP_LIST_EDIT'],
	'CACHE_TYPE' => $arParams['CACHE_TYPE'],
	'CACHE_TIME' => $arParams['CACHE_TIME'],
	'LINE_ELEMENT_COUNT' => 3,
	'SOCNET_GROUP_ID' => $arResult['VARIABLES']['group_id'],
	'TITLE_TEXT' => Loc::getMessage('LISTS_SOCNET_TAB'),
];

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => [
			'bitrix:lists.element.navchain',
			'bitrix:socialnetwork.copy.checker',
			'bitrix:lists.lists',
		],
		'POPUP_COMPONENT_TEMPLATE_NAME' => [
			'.default',
			'',
			'.default',
		],
		'POPUP_COMPONENT_PARAMS' => [
			$navChainComponentParams,
			$copyCheckerComponentParams,
			$listsComponentParams,
		],
		'POPUP_COMPONENT_PARENT' => $component,
		'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
		'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_TYPE' => 'SONET_GROUP',
		'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_ID' => $arResult['VARIABLES']['group_id'],
		'USE_UI_TOOLBAR' => 'Y',
		'UI_TOOLBAR_FAVORITES_TITLE_TEMPLATE' => $arResult['PAGES_TITLE_TEMPLATE'],
	]
);
