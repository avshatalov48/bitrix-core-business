<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CAllMain $APPLICATION*/
/** @var array $arResult*/
/** @var array $arParams*/

global $APPLICATION;
$componentParameters = array(
	'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
	'PATH_TO_USER_PROFILE' => $arResult['PATH_TO_USER_PROFILE'],
	'PATH_TO_LIST' => $arResult['PATH_TO_LIST'],
	'PATH_TO_EDIT' => $arResult['PATH_TO_EDIT'],
	'PATH_TO_ADD' => $arResult['PATH_TO_ADD'],
	'PATH_TO_CONDITIONS' => $arResult['PATH_TO_CONDITIONS'],
	'PATH_TO_CHAIN' => $arResult['PATH_TO_CHAIN'],
	'PATH_TO_STAT' => $arResult['PATH_TO_STAT'],
	'PATH_TO_RECIPIENT' => str_replace('#id#', $arResult['ID'], $arResult['PATH_TO_RECIPIENT']),
);
if ($_REQUEST['IFRAME'] == 'Y')
{
	$componentParameters['IFRAME'] = $_REQUEST['IFRAME'] == 'Y' ? 'Y' : 'N';
	$APPLICATION->IncludeComponent(
		"bitrix:sender.pageslider.wrapper",
		"",
		array(
			'POPUP_COMPONENT_NAME' => "bitrix:sender.trigger.stat",
			"POPUP_COMPONENT_TEMPLATE_NAME" => "",
			"POPUP_COMPONENT_PARAMS" => $componentParameters,
		)
	);
}
else
{
	$APPLICATION->IncludeComponent(
		"bitrix:sender.trigger.stat",
		"",
		$componentParameters
	);
}