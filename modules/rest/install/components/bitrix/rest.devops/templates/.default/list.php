<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var \CAllMain $APPLICATION*/
/** @var \CBitrixComponentTemplate $this*/
/** @var array $arResult*/
/** @var array $arParams*/

global $APPLICATION;
$APPLICATION->SetTitle($arResult['TITLE']);
$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	array(
		'POPUP_COMPONENT_NAME' => 'bitrix:rest.integration.grid',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'PATH_TO_EDIT' => $arResult['PATH_TO_EDIT'],
		],
		'USE_PADDING' => true,
		'PAGE_MODE'=> false,
		'PAGE_MODE_OFF_BACK_URL' =>	$arResult['PATH_TO_INDEX'],
		"POPUP_COMPONENT_PARENT" => $this->getComponent()
	)
);