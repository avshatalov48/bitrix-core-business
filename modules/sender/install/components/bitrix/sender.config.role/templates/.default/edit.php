<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CMain $APPLICATION*/
/** @var array $arResult*/
/** @var array $arParams*/

global $APPLICATION;

\Bitrix\Main\Loader::includeModule('socialnetwork');
$componentParams = $arParams['POPUP_COMPONENT_PARAMS'] ?? [];

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:sender.config.role.edit',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'ID'                   => $arResult['ID'],
			'NAME_TEMPLATE'        => $arResult['NAME_TEMPLATE'],
			'PATH_TO_USER_PROFILE' => $arResult['PATH_TO_CONSENTS'],
			'PATH_TO_EDIT'         => $arResult['PATH_TO_EDIT'],
			'SET_TITLE'            => 'Y',
		],
		'USE_UI_TOOLBAR' => 'Y',
		'USE_PADDING' => false,
		'PLAIN_VIEW' => false,
		'PAGE_MODE' => false,
		'PAGE_MODE_OFF_BACK_URL' => "/marketing/"
	]
);
