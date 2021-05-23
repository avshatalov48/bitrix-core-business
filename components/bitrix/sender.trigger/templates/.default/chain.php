<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var \CAllMain $APPLICATION*/
/** @var array $arResult*/
/** @var array $arParams*/

global $APPLICATION;
$componentParameters = array(
	'ID' => $arResult['ID'],
	'PATH_TO_LIST' => $arResult['PATH_TO_LIST'],
	'PATH_TO_EDIT' => $arResult['PATH_TO_EDIT'],
	'PATH_TO_ADD' => $arResult['PATH_TO_ADD'],
	'PATH_TO_CONDITIONS' => $arResult['PATH_TO_CONDITIONS'],
	'PATH_TO_CHAIN' => $arResult['PATH_TO_CHAIN'],
	'PATH_TO_STAT' => $arResult['PATH_TO_STAT'],
	'PATH_TO_RECIPIENT' => $arResult['PATH_TO_RECIPIENT'],
	'PATH_TO_LETTER_ADD' => str_replace(['#id#', '#letter_id#'], ['#campaign_id#', '#id#'], $arResult['PATH_TO_LETTER_ADD']),
	'PATH_TO_LETTER_EDIT' => str_replace(['#id#', '#letter_id#'], ['#campaign_id#', '#id#'], $arResult['PATH_TO_LETTER_EDIT']),
);
if ($_REQUEST['IFRAME'] == 'Y')
{
	$APPLICATION->IncludeComponent(
		"bitrix:sender.pageslider.wrapper",
		"",
		array(
			'POPUP_COMPONENT_NAME' => "bitrix:sender.trigger.chain",
			"POPUP_COMPONENT_TEMPLATE_NAME" => "",
			"POPUP_COMPONENT_PARAMS" => $componentParameters,
		)
	);
}
else
{
	$APPLICATION->IncludeComponent(
		"bitrix:sender.trigger.chain",
		"",
		$componentParameters
	);
}