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
		'POPUP_COMPONENT_NAME' => 'bitrix:rest.statistic',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '.default',
		'POPUP_COMPONENT_PARAMS' => [
			'ONLY_ACTIVE' => 'Y'
		],
		'USE_PADDING' => true,
		'PAGE_MODE' => false,
		'PAGE_MODE_OFF_BACK_URL' =>	$arResult['PATH_TO_INDEX'],
		"POPUP_COMPONENT_PARENT" => $this->getComponent()
	)
);