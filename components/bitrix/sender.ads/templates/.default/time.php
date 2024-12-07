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
	'PATH_TO_USER_PROFILE' => $arResult['PATH_TO_CONSENTS'] ?? null,
	'PATH_TO_LIST' => $arResult['PATH_TO_LIST'],
	'PATH_TO_EDIT' => $arResult['PATH_TO_EDIT'],
	'PATH_TO_TIME' => $arResult['PATH_TO_TIME'],
	'SET_TITLE' => 'Y',
	'CAN_VIEW' => Security\Access::getInstance()->canViewAds(),
	'CAN_EDIT' => Security\Access::getInstance()->canStopStartPause("Ad"),
	'MESS' => [
		'SENDER_LETTER_TIME_TMPL_TITLE_NEW' => Loc::getMessage('SENDER_ADS_LETTER_TIME_TMPL_TITLE_NEW'),
		'SENDER_LETTER_TIME_TMPL_TITLE_EXISTS' => Loc::getMessage('SENDER_ADS_LETTER_TIME_TMPL_TITLE_EXISTS'),
		'SENDER_LETTER_TIME_TMPL_ACT_SEND' => Loc::getMessage('SENDER_ADS_LETTER_TIME_TMPL_ACT_SEND'),
		'SENDER_LETTER_TIME_TMPL_ACT_SENT' => Loc::getMessage('SENDER_ADS_LETTER_TIME_TMPL_ACT_SENT'),
		'SENDER_LETTER_TIME_TMPL_DATE_SEND' => Loc::getMessage('SENDER_ADS_LETTER_TIME_TMPL_DATE_SEND'),
		'SENDER_LETTER_TIME_COMP_TITLE' => Loc::getMessage('SENDER_ADS_LETTER_TIME_TMPL_ACT_SEND')
	],
	'MESSAGE_CODE_LIST' => array_merge(\Bitrix\Sender\Message\Factory::getAdsMessageCodes(),
		\Bitrix\Sender\Message\Factory::getMarketingMessageCodes()),
);
if ($_REQUEST['IFRAME'] == 'Y')
{
	$APPLICATION->IncludeComponent(
		"bitrix:sender.pageslider.wrapper",
		"",
		array(
			'POPUP_COMPONENT_NAME' => "bitrix:sender.letter.time",
			"POPUP_COMPONENT_TEMPLATE_NAME" => "",
			"POPUP_COMPONENT_PARAMS" => $componentParameters,
		)
	);
}
else
{
	$APPLICATION->IncludeComponent(
		"bitrix:sender.letter.time",
		"",
		$componentParameters
	);
}