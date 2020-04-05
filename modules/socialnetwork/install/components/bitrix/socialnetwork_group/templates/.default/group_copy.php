<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

$pageId = "group_copy";

include("util_group_menu.php");

$componentParameters = [
	"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
	"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
];

if ($_REQUEST['IFRAME'] == 'Y')
{
	$APPLICATION->IncludeComponent(
		"bitrix:socialnetwork.pageslider.wrapper",
		"",
		array(
			'POPUP_COMPONENT_NAME' => "bitrix:socialnetwork.group_copy",
			"POPUP_COMPONENT_TEMPLATE_NAME" => "",
			"POPUP_COMPONENT_PARAMS" => $componentParameters,
		)
	);
}
else
{
	$APPLICATION->IncludeComponent(
		"bitrix:socialnetwork.group_copy",
		"",
		$componentParameters
	);
}