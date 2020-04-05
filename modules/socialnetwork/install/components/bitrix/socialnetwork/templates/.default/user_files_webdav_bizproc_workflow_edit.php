<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if ($arResult["VARIABLES"]["PERMISSION"] < "W")
	return false;
if ($_SERVER['REQUEST_METHOD'] == "POST" && check_bitrix_sessid())
	WDClearComponentCache(array("webdav.section.list", "webdav.menu"));
if ($arParams["SET_NAV_CHAIN"] != "N")
	$GLOBALS["APPLICATION"]->AddChainItem(GetMessage("WD_BP"), 
		CComponentEngine::MakePathFromTemplate($arResult["~PATH_TO_USER_FILES_WEBDAV_BIZPROC_WORKFLOW_ADMIN"], array()));
?><?$APPLICATION->IncludeComponent("bitrix:bizproc.workflow.edit", ".default", Array(
	"MODULE_ID" => $arResult["VARIABLES"]["MODULE_ID"], 
	"ENTITY" => $arResult["VARIABLES"]["ENTITY"], 
	"DOCUMENT_TYPE" => $arResult["VARIABLES"]["DOCUMENT_TYPE"], 
	"ID" => $arResult['VARIABLES']['ID'],
	"EDIT_PAGE_TEMPLATE" => $arResult["~PATH_TO_USER_FILES_WEBDAV_BIZPROC_WORKFLOW_EDIT"], 
	"LIST_PAGE_URL" => $arResult["~PATH_TO_USER_FILES_WEBDAV_BIZPROC_WORKFLOW_ADMIN"], 
	"SHOW_TOOLBAR" => "Y",
	"SET_TITLE" => $arParams["SET_TITLE"]
	)
);
?>