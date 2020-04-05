<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$db_res = $arParams["OBJECT"]->_get_mixed_list(null, $arParams + array("SHOW_VERSION" => "Y"), $arResult["VARIABLES"]["ELEMENT_ID"]); 
if (!($db_res && $arResult["ELEMENT"] = $db_res->GetNext()))
{
	if ($arParams["SET_STATUS_404"] == "Y"):
		CHTTP::SetStatus("404 Not Found");
	endif;
	return 0;
}
elseif ($arParams["OBJECT"]->permission < "W")
{
	return 0;
}

if ($arParams["SET_NAV_CHAIN"] != "N")
{
		
	$arResult["NAV_CHAIN"] = $arParams["OBJECT"]->GetNavChain(array("element_id" => ($arResult["ELEMENT"]["WF_PARENT_ELEMENT_ID"] > 0 ? 
		$arResult["ELEMENT"]["WF_PARENT_ELEMENT_ID"] : $arResult["ELEMENT"]["ID"])), "array");

	$arNavChain = array(); 
	foreach ($arResult["NAV_CHAIN"] as $res)
	{
		$arNavChain[] = $res["URL"];
		if (count($arNavChain) >= count($arResult["NAV_CHAIN"]))
			break; 
		$url = CComponentEngine::MakePathFromTemplate($arResult["~PATH_TO_GROUP_FILES_ELEMENT_EDIT"], 
			array("PATH" => implode("/", $arNavChain), "SECTION_ID" => $res["ID"], "ELEMENT_ID" => "files"));
		$GLOBALS["APPLICATION"]->AddChainItem(htmlspecialcharsEx($res["NAME"]), $url);
	}
	
	if ($arResult["ELEMENT"]["WF_PARENT_ELEMENT_ID"] > 0)
	{
		$GLOBALS["APPLICATION"]->AddChainItem(GetMessage("WD_ORIGINAL").": ".htmlspecialcharsEx($res["NAME"]), 
			CComponentEngine::MakePathFromTemplate($arResult["~PATH_TO_GROUP_FILES_ELEMENT_VERSIONS"], 
				array("SECTION_ID" => $res["IBLOCK_SECTION_ID"], "ELEMENT_ID" => $res["ID"])));
	}
	$GLOBALS["APPLICATION"]->AddChainItem(htmlspecialcharsEx($arResult["ELEMENT"]["NAME"]));
}
?><?$APPLICATION->IncludeComponent("bitrix:bizproc.log", "webdav.bizproc.log", Array(
	"MODULE_ID" => $arResult["VARIABLES"]["MODULE_ID"], 
	"ENTITY" => $arResult["VARIABLES"]["ENTITY"], 
    "COMPONENT_VERSION" => 2,
	"DOCUMENT_TYPE" => $arResult["VARIABLES"]["DOCUMENT_TYPE"], 
	"DOCUMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"], 
	"ID" => $arResult["VARIABLES"]["ID"],
	"DOCUMENT_URL" => str_replace(
		array("#ELEMENT_ID#", "#WORKFLOW_ID#", "#ELEMENT_NAME#"), 
		array($arResult["VARIABLES"]["ELEMENT_ID"], "#WORKFLOW_ID#", "#NAME#"), $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_HISTORY_GET"]),
	"SET_TITLE"	=>	$arParams["SET_TITLE"], 
	
	"USER_VIEW_URL" => $arResult["~PATH_TO_GROUP"]),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>
