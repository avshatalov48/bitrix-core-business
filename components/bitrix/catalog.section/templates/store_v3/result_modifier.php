<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$arParams['SHOW_SECTIONS'] = $arParams['SHOW_SECTIONS'] ?? 'Y';
$arParams['SHOW_SECTIONS'] = $arParams['SHOW_SECTIONS'] === 'N' ? 'N' : 'Y';

if (!isset($arParams['SECTIONS_OFFSET_MODE']))
{
	$arParams['SECTIONS_OFFSET_MODE'] = 'N';
}
if (
	$arParams['SECTIONS_OFFSET_MODE'] !== 'N'
	&& $arParams['SECTIONS_OFFSET_MODE'] !== 'F'
	&& $arParams['SECTIONS_OFFSET_MODE'] !== 'D'
)
{
	$arParams['SECTIONS_OFFSET_MODE'] = 'N';
}
switch ($arParams['SECTIONS_OFFSET_MODE'])
{
	case 'F':
		$arParams['SECTIONS_OFFSET_VARIABLE'] = '';
		break;
	case 'D':
		if (!isset($arParams['SECTIONS_OFFSET_VARIABLE']))
		{
			$arParams['SECTIONS_OFFSET_VARIABLE'] = '';
		}
		if (
			$arParams['SECTIONS_OFFSET_VARIABLE'] === ''
			|| !preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/', $arParams['SECTIONS_OFFSET_VARIABLE'])
		)
		{
			$arParams['SECTIONS_OFFSET_MODE'] = 'N';
			$arParams['SECTIONS_OFFSET_VARIABLE'] = '';
		}
		break;
	case 'N':
		$arParams['SECTIONS_OFFSET_VALUE'] = 0;
		$arParams['SECTIONS_OFFSET_VARIABLE'] = '';
		break;
}
if (!isset($arParams['SECTIONS_FILTER_NAME']))
{
	$arParams['SECTIONS_FILTER_NAME'] = '';
}
if (
	$arParams['SECTIONS_FILTER_NAME'] !== ''
	&& !preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/', $arParams['SECTIONS_FILTER_NAME'])
)
{
	$arParams['SECTIONS_FILTER_NAME'] = '';
}
if (!isset($arParams['SECTIONS_SECTION_ID']))
{
	$arParams['SECTIONS_SECTION_ID'] = '';
}
if (!isset($arParams['SECTIONS_SECTION_CODE']))
{
	$arParams['SECTIONS_SECTION_CODE'] = '';
}
$arParams['SECTIONS_TOP_DEPTH'] = (isset($arParams['SECTIONS_TOP_DEPTH']) ? (int)$arParams['SECTIONS_TOP_DEPTH'] : 2);
if ($arParams['SECTIONS_TOP_DEPTH'] <= 0)
{
	$arParams['SECTIONS_TOP_DEPTH'] = 2;
}

if (
	!isset($arParams['CYCLIC_LOADING'])
	|| $arParams['CYCLIC_LOADING'] !== 'Y'
	|| !empty($arParams['EXTERNAL_PRODUCT_MAP'])
)
{
	$arParams['CYCLIC_LOADING'] = 'N';
}

if (!empty($arResult['NAV_RESULT']))
{
	if ((int)$arResult['NAV_RESULT']->NavPageCount < 2)
	{
		$arParams['CYCLIC_LOADING'] = 'N';
	}
}

if ($arParams['CYCLIC_LOADING'] === 'N')
{
	$arParams['CYCLIC_LOADING_COUNTER_NAME'] = '';
}
else
{
	if (!isset($arParams['CYCLIC_LOADING_COUNTER_NAME']))
	{
		$arParams['CYCLIC_LOADING_COUNTER_NAME'] = '';
	}
	if (
		$arParams['CYCLIC_LOADING_COUNTER_NAME'] === ''
		|| !preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/', $arParams['CYCLIC_LOADING_COUNTER_NAME'])
	)
	{
		$arParams['CYCLIC_LOADING_COUNTER_NAME'] = 'cycleCount';
	}
}

if (!isset($arParams['USE_OFFER_NAME']) || $arParams['USE_OFFER_NAME'] !== 'Y')
{
	$arParams['USE_OFFER_NAME'] = 'N';
}

$arResult['ORIGINAL_PARAMETERS']['DEFERRED_LOAD'] = 'N';
$arResult['ORIGINAL_PARAMETERS']['CYCLIC_COUNT'] = 0;

$addAreaId = '';
if ($arParams['CYCLIC_LOADING'] === 'Y')
{
	$request = \Bitrix\Main\Context::getCurrent()->getRequest();
	$cyclicCount = $request->get($arParams['CYCLIC_LOADING_COUNTER_NAME']);
	$cyclicCount = (is_string($cyclicCount) ? (int)$cyclicCount : 0);
	if (!empty($arResult['ITEMS']))
	{
		$addAreaId = '_'.$cyclicCount;
		foreach (array_keys($arResult['ITEMS']) as $index)
		{
			$item = $arResult['ITEMS'][$index];
			if (!empty($item['MORE_PHOTO']) && is_array($item['MORE_PHOTO']))
			{
				$item['MORE_PHOTO_SELECTED'] = $cyclicCount % count($item['MORE_PHOTO']);
			}
			if (
				!empty($item['PRODUCT']['USE_OFFERS'])
				&& $arParams['PRODUCT_DISPLAY_MODE'] === 'Y'
			)
			{
				$offersCount = count($item['OFFERS']);
				$item['OFFERS_SELECTED'] = (
					$offersCount > 0
						? ($item['OFFERS_SELECTED'] + $cyclicCount) % $offersCount
						: 0
				);
				foreach ($item['OFFERS'] as $offerIndex => $offer)
				{
					if (!empty($offer['MORE_PHOTO']) && is_array($offer['MORE_PHOTO']))
					{
						$offer['MORE_PHOTO_SELECTED'] =
							intdiv($cyclicCount, count($item['OFFERS']))
							% count($offer['MORE_PHOTO'])
						;
						$item['JS_OFFERS'][$offerIndex]['MORE_PHOTO_SELECTED'] = $offer['MORE_PHOTO_SELECTED'];
					}
					$item['OFFERS'][$offerIndex] = $offer;
				}
			}
			$arResult['ITEMS'][$index] = $item;
		}
	}
	$arResult['ORIGINAL_PARAMETERS']['CYCLIC_COUNT'] = $cyclicCount;
}
$arResult['AREA_ID_ADDITIONAL_SALT'] = $addAreaId;