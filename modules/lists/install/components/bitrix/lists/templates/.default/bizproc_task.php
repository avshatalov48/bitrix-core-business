<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent("bitrix:bizproc.task", ".default", array(
	"DOCUMENT_URL" => str_replace(
				array("#list_id#", "#section_id#", "#element_id#"),
				array($arResult["VARIABLES"]["list_id"], intval($arResult["VARIABLES"]["section_id"]), $arResult["VARIABLES"]["element_id"]),
				$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["list_element_edit"]
			),
	"TASK_EDIT_URL" => str_replace(
				array("#list_id#", "#section_id#", "#element_id#"),
				array($arResult["VARIABLES"]["list_id"], intval($arResult["VARIABLES"]["section_id"]), $arResult["VARIABLES"]["element_id"]),
				$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_task"]
			),
	"TASK_ID" => $arResult["VARIABLES"]["task_id"],
	),
	$component
);?>