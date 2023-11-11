<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CMain $APPLICATION*/
/** @var CBitrixComponentTemplate $this*/
/** @var array $arResult*/
/** @var array $arParams*/

global $APPLICATION;
$APPLICATION->SetTitle($arResult['TITLE']);
$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	array(
		'POPUP_COMPONENT_NAME' => 'bitrix:rest.integration.list',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			"CACHE_TIME" => "3600",
			"CACHE_TYPE" => "Y",
			"TYPE" => 'LIST',
			'CODE' => $arResult['SECTION_CODE'],
			'PATH_TO_ADD' => $arResult['PATH_TO_ADD'],
		],
		'USE_PADDING' => false,
		'PAGE_MODE'=> false,
		'PAGE_MODE_OFF_BACK_URL' =>	$arResult['PATH_TO_INDEX'],
	)
);