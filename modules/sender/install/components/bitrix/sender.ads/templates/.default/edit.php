<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CAllMain $APPLICATION*/
/** @var array $arResult*/
/** @var array $arParams*/

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Security;

Loc::loadMessages(__FILE__);

global $APPLICATION;
$componentParameters = array(
	'ID' => $arResult['ID'],
	'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
	'PATH_TO_USER_PROFILE' => $arResult['PATH_TO_CONSENTS'],
	'PATH_TO_LIST' => $arResult['PATH_TO_LIST'],
	'PATH_TO_ADD' => $arResult['PATH_TO_ADD'],
	'PATH_TO_EDIT' => $arResult['PATH_TO_EDIT'],
	'PATH_TO_TIME' => $arResult['PATH_TO_TIME'],
	'PATH_TO_STAT' => $arResult['PATH_TO_STAT'],
	'PATH_TO_SEGMENT_ADD' => $arParams['PATH_TO_SEGMENT_ADD'],
	'PATH_TO_SEGMENT_EDIT' => $arParams['PATH_TO_SEGMENT_EDIT'],
	'PATH_TO_CAMPAIGN_ADD' => $arParams['PATH_TO_CAMPAIGN_ADD'],
	'PATH_TO_CAMPAIGN_EDIT' => $arParams['PATH_TO_CAMPAIGN_EDIT'],
	'SHOW_CAMPAIGNS' => $arParams['SHOW_CAMPAIGNS'],
	'SET_TITLE' => 'Y',
	'CAN_VIEW' => $arParams['CAN_VIEW'],
	'CAN_EDIT' => $arParams['CAN_EDIT'],
	'MESS' => [
		'SENDER_SEGMENT_SELECTOR_INCLUDE_EDIT_TITLE' => Loc::getMessage('SENDER_RC_SEGMENT_SELECTOR_INCLUDE_EDIT_TITLE'),
		'SENDER_SEGMENT_SELECTOR_RECIPIENT_COUNT' => Loc::getMessage('SENDER_RC_SEGMENT_SELECTOR_RECIPIENT_COUNT'),
		'SENDER_SEGMENT_SELECTOR_RECIPIENT_COUNT_HINT' => Loc::getMessage('SENDER_RC_SEGMENT_SELECTOR_RECIPIENT_COUNT_HINT'),
		'SENDER_SEGMENT_SELECTOR_RECIPIENT_COUNT_EXACT_HINT1' => Loc::getMessage('SENDER_RC_SEGMENT_SELECTOR_RECIPIENT_COUNT_EXACT_HINT1'),
	],
	'MESSAGE_CODE_LIST' => \Bitrix\Sender\Message\Factory::getAdsMessageCodes(),
);
if ($_REQUEST['IFRAME'] == 'Y')
{
	$APPLICATION->IncludeComponent(
		"bitrix:sender.pageslider.wrapper",
		"",
		array(
			'POPUP_COMPONENT_NAME' => "bitrix:sender.letter.edit",
			"POPUP_COMPONENT_TEMPLATE_NAME" => "",
			"POPUP_COMPONENT_PARAMS" => $componentParameters,
		)
	);
}
else
{
	$APPLICATION->IncludeComponent(
		"bitrix:sender.letter.edit",
		"",
		$componentParameters
	);
}