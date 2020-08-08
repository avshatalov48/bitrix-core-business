<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @global CMain $APPLICATION
 */

if(!empty($arResult['ITEMS']))
{
	$arResult['ITEMS_JS'] = array_map(
		function($item)
		{
			return [
				'id' => $item['ID'],
				'title' => $item['NAME'],
				'description' => $item['SHORT_DESC'],
				'link' => $item['URL'],
				'infoHelperCode' => $item['INFO_HELPER_CODE']? : false,
				'icon' => $item['ICON'],
				'price' => $item['PRICE'],
			];
		},
		$arResult['ITEMS']
	);
}