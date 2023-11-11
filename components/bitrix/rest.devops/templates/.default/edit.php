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
		'POPUP_COMPONENT_NAME' => 'bitrix:rest.integration.edit',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'ID' => $arResult['ID'],
			'ELEMENT_CODE' => $arResult['ELEMENT_CODE'],
			'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'] ?? null,
			'PATH_TO_IFRAME' => $arResult['PATH_TO_IFRAME'] ?? null,
			'PATH_TO_EDIT' => $arResult['PATH_TO_EDIT'] ?? null,
			'PATH_TO_GRID' => $arResult['PATH_TO_GRID'] ?? null
		],
		'USE_PADDING' => true,
		'EDITABLE_TITLE_SELECTOR'=>'#integrationEditTitle',
		'PAGE_MODE' => false,
		"USE_UI_TOOLBAR" => "N",
		'PAGE_MODE_OFF_BACK_URL' =>	$arResult['PATH_TO_LIST'],
		"POPUP_COMPONENT_PARENT" => $this->getComponent()
	)
);