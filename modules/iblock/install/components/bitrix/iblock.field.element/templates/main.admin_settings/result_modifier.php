<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Iblock\UserField\Types\ElementType;

if($arResult['additionalParameters']['bVarsFromForm'])
{
	$iblockId = $GLOBALS[$arResult['additionalParameters']['NAME']]['IBLOCK_ID'];
	$activeFilter = (
		$GLOBALS[$arResult['additionalParameters']['NAME']]['ACTIVE_FILTER'] === 'Y' ? 'Y' : 'N'
	);
	$value = $GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE'];
	$display = $GLOBALS[$arResult['additionalParameters']['NAME']]['DISPLAY'];
	$listHeight = (int)$GLOBALS[$arResult['additionalParameters']['NAME']]['LIST_HEIGHT'];
}
elseif(is_array($arResult['userField']))
{
	$iblockId = $arResult['userField']['SETTINGS']['IBLOCK_ID'];
	$activeFilter =	(
		$arResult['userField']['SETTINGS']['ACTIVE_FILTER'] === 'Y' ? 'Y' : 'N'
	);
	$value = $arResult['userField']['SETTINGS']['DEFAULT_VALUE'];
	$display = $arResult['userField']['SETTINGS']['DISPLAY'];
	$listHeight = (int)$arResult['userField']['SETTINGS']['LIST_HEIGHT'];
}
else
{
	$iblockId = '';
	$activeFilter = 'N';
	$value = '';
	$display = ElementType::DISPLAY_LIST;
	$listHeight = 5;
}

/**
 * @var $component ElementUfComponent
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
			$filter['ACTIVE'] = 'Y';
		}

		$rs = CIBlockElement::GetList(
			['NAME' => 'ASC', 'ID' => 'ASC'],
			$filter,
			false,
			false,
			['ID', 'NAME']
		);

		$options = [];
		while($ar = $rs->GetNext())
		{
			$options[$ar['ID']] = $ar['NAME'];
		}

		$arResult['options'] = $options;
	}
}

$arResult['iblockId'] = $iblockId;
$arResult['activeFilter'] = $activeFilter;
$arResult['value'] = $value;
$arResult['display'] = $display;
$arResult['listHeight'] = $listHeight;