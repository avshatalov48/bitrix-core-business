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
	'PATH_TO_ADD' => $arResult['PATH_TO_ADD'],
	'PATH_TO_EDIT' => $arResult['PATH_TO_EDIT'],
	'PATH_TO_TIME' => $arResult['PATH_TO_TIME'],
	'PATH_TO_STAT' => $arResult['PATH_TO_STAT'],
	'PATH_TO_SEGMENT_ADD' => $arParams['PATH_TO_SEGMENT_ADD'],
	'PATH_TO_SEGMENT_EDIT' => $arParams['PATH_TO_SEGMENT_EDIT'],
	'SET_TITLE' => 'Y',
	'SHOW_SEGMENT_COUNTERS' => false,
	'CAN_VIEW' => Security\Access::getInstance()->canViewRc(),
	'CAN_EDIT' => Security\Access::getInstance()->canModifyRc(),
	'MESS' => [
		'SENDER_SEGMENT_SELECTOR_INCLUDE_VIEW_TITLE' => Loc::getMessage('SENDER_RC_SEGMENT_SELECTOR_INCLUDE_VIEW_TITLE'),
		'SENDER_SEGMENT_SELECTOR_INCLUDE_EDIT_TITLE' => Loc::getMessage('SENDER_RC_SEGMENT_SELECTOR_INCLUDE_EDIT_TITLE'),
		'SENDER_SEGMENT_SELECTOR_RECIPIENT_COUNT' => Loc::getMessage('SENDER_RC_SEGMENT_SELECTOR_RECIPIENT_COUNT'),
		'SENDER_SEGMENT_SELECTOR_RECIPIENT_COUNT_HINT' => Loc::getMessage('SENDER_RC_SEGMENT_SELECTOR_RECIPIENT_COUNT_HINT'),
		'SENDER_SEGMENT_SELECTOR_RECIPIENT_COUNT_EXACT_HINT1' => Loc::getMessage('SENDER_RC_SEGMENT_SELECTOR_RECIPIENT_COUNT_EXACT_HINT1'),
		'SENDER_COMP_LETTER_EDIT_TITLE_TEMPLATES' => Loc::getMessage('SENDER_RC_COMP_LETTER_EDIT_TITLE_TEMPLATES'),
		'SENDER_COMP_LETTER_EDIT_TITLE_EDIT' => Loc::getMessage('SENDER_RC_COMP_LETTER_EDIT_TITLE_EDIT'),
		'SENDER_COMP_LETTER_EDIT_TITLE_ADD' => Loc::getMessage('SENDER_RC_COMP_LETTER_EDIT_TITLE_ADD'),
	],
	'MESSAGE_CODE_LIST' => \Bitrix\Sender\Message\Factory::getReturnCustomerMessageCodes(),
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