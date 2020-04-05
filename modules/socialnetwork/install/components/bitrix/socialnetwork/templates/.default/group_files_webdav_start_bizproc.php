<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent("bitrix:bizproc.workflow.start", "", Array(
	"MODULE_ID" => $arResult["VARIABLES"]["MODULE_ID"], 
	"ENTITY" => $arResult["VARIABLES"]["ENTITY"], 
	"DOCUMENT_TYPE" => $arResult["VARIABLES"]["DOCUMENT_TYPE"], 
	"DOCUMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
	"TEMPLATE_ID" => $arResult["VARIABLES"]["TEMPLATE_ID"], 
	"SET_TITLE"	=>	$arParams["SET_TITLE"]),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>