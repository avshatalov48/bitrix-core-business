<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var \CAllMain $APPLICATION*/
/** @var array $arResult*/
/** @var array $arParams*/

global $APPLICATION;
$componentParameters = array(
	'SET_TITLE' => 'Y',
);
if (isset($_REQUEST['IFRAME']) && $_REQUEST['IFRAME'] == 'Y')
{
	$APPLICATION->IncludeComponent(
		"bitrix:sender.pageslider.wrapper",
		"",
		array(
			'POPUP_COMPONENT_NAME' => "bitrix:sender.abuse.list",
			"POPUP_COMPONENT_TEMPLATE_NAME" => "",
			"POPUP_COMPONENT_PARAMS" => $componentParameters,
			"BUTTON_LIST" => ['CLOSE' => ['URL' => '']]
		)
	);
}
else
{
	$APPLICATION->IncludeComponent(
		"bitrix:sender.abuse.list",
		"",
		$componentParameters
	);
}