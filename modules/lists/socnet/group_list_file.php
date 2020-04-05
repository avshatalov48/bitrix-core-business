<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent("bitrix:lists.file", ".default", array(
	"IBLOCK_TYPE_ID" => COption::GetOptionString("lists", "socnet_iblock_type_id"),
	"IBLOCK_ID" => $arResult["VARIABLES"]["list_id"],
	"SECTION_ID" => $arResult["VARIABLES"]["section_id"],
	"ELEMENT_ID" => $arResult["VARIABLES"]["element_id"],
	"FIELD_ID" => $arResult["VARIABLES"]["field_id"],
	"FILE_ID" => $arResult["VARIABLES"]["file_id"],
	"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
	),
	$component
);?>