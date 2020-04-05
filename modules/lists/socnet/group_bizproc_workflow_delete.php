<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent("bitrix:lists.element.navchain", ".default", array(
	"IBLOCK_TYPE_ID" => COption::GetOptionString("lists", "socnet_iblock_type_id"),
	"IBLOCK_ID" => $arResult["VARIABLES"]["list_id"],
	"SECTION_ID" => $arResult["VARIABLES"]["section_id"],
	"ELEMENT_ID" => $arResult["VARIABLES"]["element_id"],
	"LISTS_URL" => $arResult["PATH_TO_GROUP_LISTS"],
	"LIST_URL" => $arResult["PATH_TO_GROUP_LIST_VIEW"],
	"LIST_ELEMENT_URL" => $arResult["PATH_TO_GROUP_LIST_ELEMENT_EDIT"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	),
	$component
);

$moduleId = "lists";
$entity = 'Bitrix\Lists\BizprocDocumentLists';

$APPLICATION->IncludeComponent("bitrix:bizproc.document", ".default", array(
	"MODULE_ID" => $moduleId,
	"ENTITY" => $entity,
	"DOCUMENT_TYPE" => "iblock_".$arResult["VARIABLES"]["list_id"],
	"DOCUMENT_ID" => $arResult["VARIABLES"]["element_id"],
	"SET_TITLE" => "N",
	),
	$component
);
?>