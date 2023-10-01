<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CMain $APPLICATION*/
/** @var array $arResult*/
/** @var array $arParams*/

global $APPLICATION;
$componentParameters = array(
	'ID' => $arResult['LETTER_ID'],
	'CAMPAIGN_ID' => $arResult['ID'],
	'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
	'PATH_TO_USER_PROFILE' => $arResult['PATH_TO_CONSENTS'],
	'PATH_TO_LIST' => $arResult['PATH_TO_LIST'],
	'PATH_TO_ADD' => str_replace(['#id#', '#letter_id#'], [$arResult['ID'], '#id#'], $arResult['PATH_TO_LETTER_ADD']),
	'PATH_TO_EDIT' => str_replace(['#id#', '#letter_id#'], [$arResult['ID'], '#id#'], $arResult['PATH_TO_LETTER_EDIT']),
	'PATH_TO_TIME' => $arResult['PATH_TO_TIME'],
	'PATH_TO_STAT' => $arResult['PATH_TO_STAT'],
	'SHOW_SEGMENTS' => false,
	'SHOW_CAMPAIGNS' => false,
	'IS_TRIGGER' => true,
	'SET_TITLE' => 'Y',
	'MESSAGE_CODE_LIST' => \Bitrix\Sender\Message\Factory::getMailingMessageCodes(),
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