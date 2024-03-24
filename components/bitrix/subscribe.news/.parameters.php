<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/* @var array $arCurrentValues */
if (!IsModuleInstalled('iblock') || !CModule::IncludeModule('iblock'))
{
	return;
}

$arSites = [];
$defSite = '';
$rsSite = CSite::GetList();
while ($arSite = $rsSite->Fetch())
{
	$arSites[$arSite['ID']] = $arSite['NAME'];
	if ($arSite['DEF'] == 'Y')
	{
		$defSite = $arSite['ID'];
	}
}

$arIBlockTypes = [];
$defIBlockType = 'news';
$rsIBlockType = CIBlockType::GetList(['SORT' => 'ASC']);
while ($arIBlockType = $rsIBlockType->Fetch())
{
	if ($arIBlockType = CIBlockType::GetByIDLang($arIBlockType['ID'], LANGUAGE_ID))
	{
		$arIBlockTypes[$arIBlockType['ID']] = $arIBlockType['NAME'];
	}
}

$arIBlocks = ['-' => GetMessage('MAIN_ALL')];
$rsIBlock = CIBlock::GetList(
	['SORT' => 'ASC'],
	[
		'SITE_ID' => $arCurrentValues['SITE_ID'],
		'TYPE' => ($arCurrentValues['IBLOCK_TYPE'] != '-' ? $arCurrentValues['IBLOCK_TYPE'] : ''),
	]
);
while ($arIBlock = $rsIBlock->Fetch())
{
	$arIBlocks[$arIBlock['ID']] = $arIBlock['NAME'];
}

$arSorts = [
	'ASC' => GetMessage('CP_BSN_ORDER_ASC'),
	'DESC' => GetMessage('CP_BSN_ORDER_DESC'),
];
$arSortFields = [
	'ACTIVE_FROM' => GetMessage('CP_BSN_ACTIVE_FROM'),
	'SORT' => GetMessage('CP_BSN_SORT'),
];

$arComponentParameters = [
	'GROUPS' => [
	],
	'PARAMETERS' => [
		'SITE_ID' => [
			'NAME' => GetMessage('CP_BSN_SITE_ID'),
			'TYPE' => 'LIST',
			'VALUES' => $arSites,
			'DEFAULT' => $defSite,
			'REFRESH' => 'Y',
		],
		'IBLOCK_TYPE' => [
			'NAME' => GetMessage('CP_BSN_IBLOCK_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => $arIBlockTypes,
			'DEFAULT' => $defIBlockType,
			'REFRESH' => 'Y',
		],
		'ID' => [
			'NAME' => GetMessage('CP_BSN_ID'),
			'TYPE' => 'LIST',
			'VALUES' => $arIBlocks,
		],
		'SORT_BY' => [
			'NAME' => GetMessage('CP_BSN_SORT_BY'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'ACTIVE_FROM',
			'VALUES' => $arSortFields,
		],
		'SORT_ORDER' => [
			'NAME' => GetMessage('CP_BSN_SORT_ORDER'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'DESC',
			'VALUES' => $arSorts,
		],
	],
];
