<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Iblock\UserField\Types\SectionType;
use Bitrix\Main\Text\HtmlFilter;

if (isset($arResult['additionalParameters']['bVarsFromForm']) && $arResult['additionalParameters']['bVarsFromForm'])
{
	$iblockId = (int)($GLOBALS[$arResult['additionalParameters']['NAME']]['IBLOCK_ID'] ?? 0);
	$activeFilter = (
		isset($GLOBALS[$arResult['additionalParameters']['NAME']]['ACTIVE_FILTER'])
		&& $GLOBALS[$arResult['additionalParameters']['NAME']]['ACTIVE_FILTER'] === 'Y'
			? 'Y'
			: 'N'
	);
	$value = HtmlFilter::encode($GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE'] ?? '');
	$display = $GLOBALS[$arResult['additionalParameters']['NAME']]['DISPLAY'] ?? '';
	$listHeight = (int)($GLOBALS[$arResult['additionalParameters']['NAME']]['LIST_HEIGHT'] ?? 0);
}
elseif (isset($arResult['userField']) && is_array($arResult['userField']))
{
	$iblockId = (int)($arResult['userField']['SETTINGS']['IBLOCK_ID'] ?? 0);
	$activeFilter =	(
		isset($arResult['userField']['SETTINGS']['ACTIVE_FILTER'])
		&& $arResult['userField']['SETTINGS']['ACTIVE_FILTER'] === 'Y'
			? 'Y'
			: 'N'
	);
	$value = HtmlFilter::encode($arResult['userField']['SETTINGS']['DEFAULT_VALUE'] ?? '');
	$display = $arResult['userField']['SETTINGS']['DISPLAY'] ?? '';
	$listHeight = (int)($arResult['userField']['SETTINGS']['LIST_HEIGHT'] ?? 0);
}
else
{
	$iblockId = '';
	$activeFilter = 'N';
	$value = '';
	$display = SectionType::DISPLAY_LIST;
	$listHeight = 5;
}

/**
 * @var $component SectionUfComponent
 */
$component = $this->getComponent();
if($component->isIblockIncluded())
{
	$iblockId = (int)$iblockId;

	if($iblockId)
	{
		$iblockName = (string)CIBlock::GetArrayByID($iblockId, 'NAME');
		if($iblockName === '')
		{
			$iblockId = 0;
		}
	}

	if($iblockId)
	{
		$filter = ['IBLOCK_ID' => $iblockId];

		if($activeFilter === 'Y')
		{
			$filter['GLOBAL_ACTIVE'] = 'Y';
		}

		$sections = CIBlockSection::GetList(
			['left_margin' => 'asc'],
			$filter,
			false,
			['ID', 'DEPTH_LEVEL', 'NAME']
		);

		$options = [];
		while($section = $sections->GetNext())
		{
			$options[$section['ID']] = str_repeat('&nbsp;.&nbsp;', $section['DEPTH_LEVEL']) . $section['NAME'];
		}

		$arResult['options'] = $options;
	}
}

$arResult['iblockId'] = $iblockId;
$arResult['activeFilter'] = $activeFilter;
$arResult['value'] = $value;
$arResult['display'] = $display;
$arResult['listHeight'] = $listHeight;
