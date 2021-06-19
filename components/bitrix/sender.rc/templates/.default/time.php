<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CAllMain $APPLICATION*/
/** @var array $arResult*/
/** @var array $arParams*/

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Security;

global $APPLICATION;
$componentParameters = array(
	'ID' => $arResult['ID'],
	'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
	'PATH_TO_USER_PROFILE' => $arResult['PATH_TO_CONSENTS'],
	'PATH_TO_LIST' => $arResult['PATH_TO_LIST'],
	'PATH_TO_EDIT' => $arResult['PATH_TO_EDIT'],
	'SET_TITLE' => 'Y',
	'CAN_VIEW' => Security\Access::getInstance()->canViewRc(),
	'CAN_EDIT' => Security\Access::getInstance()->canStopStartPause("Rc"),
	'MESS' => [
		'SENDER_LETTER_TIME_TMPL_TITLE_NEW' => Loc::getMessage('SENDER_RC_LETTER_TIME_TMPL_TITLE_NEW'),
		'SENDER_LETTER_TIME_TMPL_TITLE_EXISTS' => Loc::getMessage('SENDER_RC_LETTER_TIME_TMPL_TITLE_EXISTS'),
		'SENDER_LETTER_TIME_TMPL_ACT_SEND' => Loc::getMessage('SENDER_RC_LETTER_TIME_TMPL_ACT_SEND'),
		'SENDER_LETTER_TIME_TMPL_ACT_SENT' => Loc::getMessage('SENDER_RC_LETTER_TIME_TMPL_ACT_SENT'),
		'SENDER_LETTER_TIME_TMPL_DATE_SEND' => Loc::getMessage('SENDER_RC_LETTER_TIME_TMPL_DATE_SEND'),
		'SENDER_LETTER_TIME_COMP_TITLE' => Loc::getMessage('SENDER_RC_LETTER_TIME_TMPL_ACT_SEND'),
		'SENDER_LETTER_TIME_OUTSIDE_ADD_SUCCESS' => Loc::getMessage('SENDER_RC_LETTER_TIME_OUTSIDE_ADD_SUCCESS'),
	],
	'MESSAGE_CODE_LIST' => \Bitrix\Sender\Message\Factory::getReturnCustomerMessageCodes(),
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