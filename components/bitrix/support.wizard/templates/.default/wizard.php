<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$template = $arParams["TEMPLATE_TYPE"]=='standard'?'standard':'';
$APPLICATION->IncludeComponent("bitrix:iblock.wizard", $template, Array(
	"IBLOCK_TYPE"	=>	$arParams["IBLOCK_TYPE"],
	"IBLOCK_ID"	=>	$arParams["IBLOCK_ID"],
	"PROPERTY_FIELD_TYPE"	=>	$arParams["PROPERTY_FIELD_TYPE"],
	"PROPERTY_FIELD_VALUES"	=>	$arParams["PROPERTY_FIELD_VALUES"],
	"BACK_URL"	=>	$arResult["BACK_URL"],
	"NEXT_URL"	=>	$arResult["NEXT_URL"],
	"INCLUDE_IBLOCK_INTO_CHAIN"	=> $arParams["INCLUDE_IBLOCK_INTO_CHAIN"],
	"SHOW_COUPON_FIELD"	=> $arParams["SHOW_COUPON_FIELD"],
	"ALLOWED_IBLOCK_SECTIONS" => $arResult['ALLOWED_IBLOCK_SECTIONS'],
	"RESTRICTED_IBLOCK_SECTIONS" => $arResult['RESTRICTED_IBLOCK_SECTIONS']
	),
	$component,
	array('HIDE_ICONS' => 'Y')
	
);?>
