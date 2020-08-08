<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Page\Asset;

if(
	$arParams['userField']['ENTITY_VALUE_ID'] <= 0
	&&
	$arParams['userField']['SETTINGS']['DEFAULT_VALUE']
)
{
	$arResult['value'] = array($arParams['userField']['SETTINGS']['DEFAULT_VALUE']);
}
else
{
	$arResult['value'] = array_filter($arResult['value']);
}