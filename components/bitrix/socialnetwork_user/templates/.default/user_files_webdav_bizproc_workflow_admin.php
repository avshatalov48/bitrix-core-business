<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if ($arResult["VARIABLES"]["PERMISSION"] < "W")
	return false;

if (check_bitrix_sessid())
{
	WDClearComponentCache(array("webdav.section.list", "webdav.menu"));

	global $CACHE_MANAGER;
	$CACHE_MANAGER->ClearByTag("iblock_id_".intval($arParams["IBLOCK_ID"])."");
}


?><?$APPLICATION->IncludeComponent("bitrix:bizproc.workflow.list", ".default", Array(
	"MODULE_ID"	=>	$arResult["VARIABLES"]["MODULE_ID"], 
	"ENTITY"	=>	$arResult["VARIABLES"]["ENTITY"], 
	"DOCUMENT_ID"	=>	$arResult["VARIABLES"]["DOCUMENT_TYPE"], 
	"EDIT_URL" => $arResult["~PATH_TO_USER_FILES_WEBDAV_BIZPROC_WORKFLOW_EDIT"],
	"SET_TITLE"	=>	$arParams["SET_TITLE"]),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>
