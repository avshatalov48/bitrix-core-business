<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var \CAllMain $APPLICATION*/
/** @var \CBitrixComponentTemplate $this*/
/** @var array $arResult*/
/** @var array $arParams*/

global $APPLICATION;
$APPLICATION->SetTitle($arResult['TITLE']);
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$query = $request->getPost("query");

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	array(
		'POPUP_COMPONENT_NAME' => 'bitrix:rest.integration.iframe',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'PATH' => $query,
			'SET_TITLE' => 'Y'
		],
		'USE_PADDING' => false,
		'PAGE_MODE'=> false,
		'PAGE_MODE_OFF_BACK_URL' =>	$arResult['PATH_TO_INDEX'],
		'BUTTONS' => ['close'],
	)
);