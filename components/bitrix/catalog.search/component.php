<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$arResult["THEME_COMPONENT"] = $this->getParent();
if(!is_object($arResult["THEME_COMPONENT"]))
	$arResult["THEME_COMPONENT"] = $this;

if (!isset($arParams['ELEMENT_SORT_FIELD2']))
	$arParams['ELEMENT_SORT_FIELD2'] = '';
if (!isset($arParams['ELEMENT_SORT_ORDER2']))
	$arParams['ELEMENT_SORT_ORDER2'] = '';
if (!isset($arParams['HIDE_NOT_AVAILABLE']))
	$arParams['HIDE_NOT_AVAILABLE'] = '';
if (!isset($arParams['OFFERS_SORT_FIELD2']))
	$arParams['OFFERS_SORT_FIELD2'] = '';
if (!isset($arParams['OFFERS_SORT_ORDER2']))
	$arParams['OFFERS_SORT_ORDER2'] = '';
if (!isset($arParams['USE_TITLE_RANK']) || $arParams['USE_TITLE_RANK'] !== 'Y')
	$arParams['USE_TITLE_RANK'] = 'N';
if (!isset($arParams['USE_SEARCH_RESULT_ORDER']) || $arParams['USE_SEARCH_RESULT_ORDER'] !== 'Y')
	$arParams['USE_SEARCH_RESULT_ORDER'] = 'N';

$this->IncludeComponentTemplate();