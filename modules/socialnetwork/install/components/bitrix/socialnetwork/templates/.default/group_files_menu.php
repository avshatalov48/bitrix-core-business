<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "group_files";
include("util_group_menu.php");
include("util_group_profile.php");
?><?
if ($arParams["FATAL_ERROR"] == "Y"):
	if (!empty($arParams["ERROR_MESSAGE"])):
		ShowError($arParams["ERROR_MESSAGE"]);
	else:
		ShowNote($arParams["NOTE_MESSAGE"], "notetext-simple");
	endif;
	$this->__component->__menu_values = false;
	return false;
endif;
if ($arResult["VARIABLES"]["PAGE_NAME"] == "WEBDAV_TASK"):
	$GLOBALS["APPLICATION"]->AddChainItem(GetMessage("WD_TASK"), 
		CComponentEngine::MakePathFromTemplate($arResult['~PATH_TO_GROUP_FILES_WEBDAV_TASK_LIST'], array()));
elseif ($arResult["VARIABLES"]["PAGE_NAME"]== "PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_WORKFLOW_EDIT"):
	$GLOBALS["APPLICATION"]->AddChainItem(GetMessage("WD_BP"), 
		CComponentEngine::MakePathFromTemplate($arResult['~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_WORKFLOW_ADMIN'], array()));
endif;
?>
<br class="sn-br" />
<?
if ($arParams["SHOW_WEBDAV"] == "Y")
{
	$url_help = $arResult["~PATH_TO_GROUP_FILES_HELP"];
	$url_base = str_replace(":443", "", rtrim($arResult["VARIABLES"]["BASE_URL"], '/')); 
	$url_base = ($GLOBALS["APPLICATION"]->IsHTTPS() ? 'https' : 'http').'://'.str_replace("//", "/", $_SERVER['HTTP_HOST']."/".$url_base."/");
	include_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/components/bitrix/webdav/templates/.default/informer.php");
}

if ($arParams["PERMISSION"] >= "W" && $arParams["CHECK_CREATOR"] != "Y" && $arResult["VARIABLES"]["PAGE_NAME"] == "SECTIONS")
{
//	$result = CSocNetUserToGroup::InitUserPerms($GLOBALS["USER"]->GetId(), $arGroup, CSocNetUser::IsCurrentUserModuleAdmin()); 
//	if ($result["UserCanModerateGroup"] === true)
//	{
		$bNeedButton = ($arParams["OBJECT"]->workflow == "bizproc"); 
		if ($arParams["OBJECT"]->workflow == "bizproc_limited")
		{
			$bNeedButton = (CIBlock::GetArrayByID($arParams["OBJECT"]->IBLOCK_ID, "BIZPROC") != "N"); 
		}
		if ($bNeedButton)
		{
			$component->arResult["arButtons"] = (is_array($component->arResult["arButtons"]) ? $component->arResult["arButtons"] : array()); 
			$component->arResult["arButtons"][] = array(
				"TEXT" => GetMessage("SOCNET_SETTINGS"),
				"TITLE" => GetMessage("SOCNET_SETTINGS_ALT"),
				"LINK" => "javascript:".$APPLICATION->GetPopupLink(Array(
					"URL" => $component->__path."/include/webdav_settings.php?DOCUMENT_ID=".$arParams["OBJECT"]->wfParams['DOCUMENT_TYPE'][2].
						"&back_url=".urlencode($APPLICATION->GetCurPage()),
					"PARAMS" => Array("min_width" => 300, "min_height" => 150)
				)),
				"ICON" => "btn-list settings"); 
		}
//	}
}

?><?$result = $APPLICATION->IncludeComponent("bitrix:webdav.menu", ".default", Array(
	"OBJECT"	=>	$arParams["OBJECT"], 
	"SECTION_ID"	=>	$arResult["VARIABLES"]["SECTION_ID"],
	"ELEMENT_ID"	=>	$arResult["VARIABLES"]["ELEMENT_ID"],
	"PAGE_NAME" => $arResult["VARIABLES"]["PAGE_NAME"],
	"ACTION"	=>	$arResult["VARIABLES"]["ACTION"],
	"BASE_URL"	=>	$arResult["VARIABLES"]["BASE_URL"],
	
	"SECTIONS_URL" => $arResult["~PATH_TO_GROUP_FILES"],
	"SECTION_EDIT_URL" => $arResult["~PATH_TO_GROUP_FILES_SECTION_EDIT"],
	"ELEMENT_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT"],
	"ELEMENT_EDIT_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_EDIT"],
	"ELEMENT_FILE_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_FILE"],
	"ELEMENT_HISTORY_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_HISTORY"],
	"ELEMENT_HISTORY_GET_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_HISTORY_GET"],
	"ELEMENT_VERSION_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_VERSION"],
	"ELEMENT_VERSIONS_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_VERSIONS"],
	"ELEMENT_UPLOAD_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_UPLOAD"],
	"HELP_URL" => $arResult["~PATH_TO_GROUP_FILES_HELP"],
	"CONNECTOR_URL" => $arResult["~PATH_TO_GROUP_FILES_CONNECTOR"],
	"USER_VIEW_URL" => $arResult["~PATH_TO_USER"],
	"WEBDAV_BIZPROC_HISTORY_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_HISTORY"], 
	"WEBDAV_BIZPROC_HISTORY_GET_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_HISTORY_GET"], 
	"WEBDAV_BIZPROC_LOG_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_LOG"], 
	"WEBDAV_BIZPROC_VIEW_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_VIEW"], 
	"WEBDAV_BIZPROC_WORKFLOW_ADMIN_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_WORKFLOW_ADMIN"], 
	"WEBDAV_BIZPROC_WORKFLOW_EDIT_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_WORKFLOW_EDIT"], 
	"WEBDAV_START_BIZPROC_URL" => $arResult["~PATH_TO_GROUP_FILES_WEBDAV_START_BIZPROC"], 
	"WEBDAV_TASK_LIST_URL" => $arResult["~PATH_TO_BIZPROC_TASK_LIST"], 
	"WEBDAV_TASK_URL" => $arResult["~PATH_TO_BIZPROC_TASK"], 
	
	"BIZPROC" => $arResult["VARIABLES"]["BIZPROC"], 
	"USE_COMMENTS"	=>	"N", 
	"FORUM_ID" => false, 

	"STR_TITLE" => $arResult["VARIABLES"]["STR_TITLE"], 
	"SHOW_WEBDAV" => $arResult["VARIABLES"]["SHOW_WEBDAV"]),
	$component,
	array("HIDE_ICONS" => "Y")
);?>
<?
$this->__component->__menu_values = $result;

if ($arParams["SHOW_NAVIGATION"] != "N")
{
// text from main
	CMain::InitPathVars($site, $path);
	$DOC_ROOT = CSite::GetSiteDocRoot($site);

	$path = $GLOBALS["APPLICATION"]->GetCurDir();
	$arChain = Array();
	
	while(true)
	{
		$path = rtrim($path, "/");

		$chain_file_name = $DOC_ROOT.$path."/.section.php";
		if(file_exists($chain_file_name))
		{
			$sSectionName = "";
			include($chain_file_name);
			if(strlen($sSectionName)>0)
				$arChain[] = Array("TITLE"=>$sSectionName, "LINK"=>$path."/");
		}

		if($path.'/' == SITE_DIR)
			break;

		if(strlen($path)<=0)
			break;

		$pos = bxstrrpos($path, "/");
		if($pos===false)
			break;
		$path = substr($path, 0, $pos+1);
	}
	
	$GLOBALS["APPLICATION"]->IncludeComponent(
		"bitrix:breadcrumb", 
		"webdav",
		Array(
			"START_FROM" => (count($arChain) + $this->__component->__count_chain_item - 1), 
			"PATH" => "", 
			"SITE_ID" => "",
			"STR_TITLE" => $arParams["STR_TITLE"]
		), $component, 
		array("HIDE_ICONS" => "Y")
	);
}
?>
