<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */


if (!\Bitrix\Main\Loader::includeModule("rest"))
{
	return;
}

$arResult = \Bitrix\Rest\Marketplace\Client::getTop($arParams['ACTION']);

$arResult['ITEMS_INSTALLED'] = array();
if(!empty($arResult['ITEMS']) && is_array($arResult['ITEMS']))
{
	$listAppCode = array();
	foreach($arResult['ITEMS'] as $catagory)
	{
		if(is_array($catagory))
		{
			foreach($catagory as $item)
			{
				$listAppCode[] = $item['CODE'];
			}
		}
	}

	if(count($listAppCode) > 0)
	{
		$dbRes = \Bitrix\Rest\AppTable::getList(array(
			'filter' => array(
				'@CODE' => $listAppCode,
				'=ACTIVE' => \Bitrix\Rest\AppTable::ACTIVE
			),
			'select' => array('CODE')
		));
		while($installedApp = $dbRes->fetch())
		{
			$arResult['ITEMS_INSTALLED'][] = $installedApp['CODE'];
		}
	}
}

$this->IncludeComponentTemplate();
