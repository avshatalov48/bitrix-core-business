<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$gridID = $arParams['GRID_ID'];
$gridContext = array();
if(isset($arParams['FILTER_FIELDS']))
{
	$gridContext = array(
		'FILTER_INFO' =>
			array(
				'ID' => isset($arParams['FILTER_FIELDS']['GRID_FILTER_ID']) ? $arParams['FILTER_FIELDS']['GRID_FILTER_ID'] : '',
				'IS_APPLIED' => isset($arParams['FILTER_FIELDS']['GRID_FILTER_APPLIED']) ? $arParams['FILTER_FIELDS']['GRID_FILTER_APPLIED'] : false
			)
	);
}
$arResult['FILTER_INFO'] = isset($gridContext['FILTER_INFO']) ? $gridContext['FILTER_INFO'] : array();
$this->IncludeComponentTemplate();