<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CMain $APPLICATION*/
/** @var array $arResult*/
/** @var array $arParams*/

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