<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CMain $APPLICATION*/
/** @var array $arResult*/
/** @var array $arParams*/

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Security;

Loc::loadMessages(__FILE__);

global $APPLICATION;
$componentParameters = array(
	'ID' => $arResult['ID'],
	'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
	'PATH_TO_USER_PROFILE' => $arResult['PATH_TO_CONSENTS'] ?? '',
	'PATH_TO_LIST' => $arResult['PATH_TO_LIST'] ?? '',
	'PATH_TO_ADD' => $arResult['PATH_TO_ADD'] ?? '',
	'PATH_TO_EDIT' => $arResult['PATH_TO_EDIT'] ?? '',
	'PATH_TO_SEGMENT_ADD' => $arParams['PATH_TO_SEGMENT_ADD'] ?? '',
	'PATH_TO_SEGMENT_EDIT' => $arParams['PATH_TO_SEGMENT_EDIT'] ?? '',
	'PATH_TO_CAMPAIGN_ADD' => $arParams['PATH_TO_CAMPAIGN_ADD'] ?? '',
	'PATH_TO_CAMPAIGN_EDIT' => $arParams['PATH_TO_CAMPAIGN_EDIT'] ?? '',
	'SHOW_CAMPAIGNS' => $arParams['SHOW_CAMPAIGNS'] ?? '',
	'SET_TITLE' => 'Y',
	'CAN_VIEW' => Security\Access::current()->canViewRc(),
	'CAN_EDIT' => Security\Access::current()->canModifyRc(),
	'MESSAGE_CODE_LIST' => \Bitrix\Sender\Message\Factory::getTolokaMessageCodes()
);

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:sender.yandex.toloka.edit',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => $componentParameters,
		'USE_UI_TOOLBAR' => 'Y',
		'USE_PADDING' => false,
		'PLAIN_VIEW' => false,
		'PAGE_MODE' => false,
		'PAGE_MODE_OFF_BACK_URL' => "/marketing/toloka/"
	]
);
