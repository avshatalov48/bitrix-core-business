<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent("bitrix:webdav.element.edit", "", Array(
	"OBJECT"	=>	$arParams["OBJECT"], 
	"IBLOCK_TYPE"	=>	$arParams["FILES_USER_IBLOCK_TYPE"],
	"IBLOCK_ID"	=>	$arParams["FILES_USER_IBLOCK_ID"],
	"ROOT_SECTION_ID"	=>	$arResult["VARIABLES"]["ROOT_SECTION_ID"],
	"SECTION_ID"	=>	$arResult["VARIABLES"]["SECTION_ID"],
	"ELEMENT_ID"	=>	$arResult["VARIABLES"]["ELEMENT_ID"],
	"PERMISSION"	=>	$arResult["VARIABLES"]["PERMISSION"],
	"CHECK_CREATOR"	=>	$arResult["VARIABLES"]["CHECK_CREATOR"],
	"NAME_FILE_PROPERTY"	=>	$arParams["NAME_FILE_PROPERTY"],
	"ACTION"	=>	$arResult["VARIABLES"]["ACTION"],
	"REPLACE_SYMBOLS"	=>	$arParams["REPLACE_SYMBOLS"],
	
	"SECTIONS_URL" => $arResult["~PATH_TO_USER_FILES_SHORT"],
	"SECTION_EDIT_URL" => $arResult["~PATH_TO_USER_FILES_SECTION_EDIT"],
	"ELEMENT_URL" => $arResult["~PATH_TO_USER_FILES_ELEMENT"],
	"ELEMENT_EDIT_URL" => $arResult["~PATH_TO_USER_FILES_ELEMENT_VERSION"],
	"ELEMENT_FILE_URL" => $arResult["~PATH_TO_USER_FILES_ELEMENT_FILE"],
	"ELEMENT_HISTORY_URL" => $arResult["~PATH_TO_USER_FILES_ELEMENT_HISTORY"],
	"ELEMENT_HISTORY_GET_URL" => $arResult["~PATH_TO_USER_FILES_ELEMENT_HISTORY_GET"],
	"ELEMENT_VERSION_URL" => $arResult["~PATH_TO_USER_FILES_ELEMENT_VERSION"],
	"ELEMENT_VERSIONS_URL" => $arResult["~PATH_TO_USER_FILES_ELEMENT_VERSIONS"],
	"ELEMENT_UPLOAD" => $arResult["~PATH_TO_USER_FILES_ELEMENT_UPLOAD"],
	"HELP_URL" => $arResult["~PATH_TO_USER_FILES_HELP"],
	"USER_VIEW_URL" => $arResult["~PATH_TO_USER"],
	"WEBDAV_BIZPROC_HISTORY_URL" => $arResult["~PATH_TO_USER_FILES_WEBDAV_BIZPROC_HISTORY"], 
	"WEBDAV_BIZPROC_HISTORY_GET_URL" => $arResult["~PATH_TO_USER_FILES_WEBDAV_BIZPROC_HISTORY_GET"], 
	"WEBDAV_BIZPROC_LOG_URL" => $arResult["~PATH_TO_USER_FILES_WEBDAV_BIZPROC_LOG"], 
	"WEBDAV_BIZPROC_VIEW_URL" => $arResult["~PATH_TO_USER_FILES_WEBDAV_BIZPROC_VIEW"], 
	"WEBDAV_BIZPROC_WORKFLOW_ADMIN_URL" => $arResult["~PATH_TO_USER_FILES_WEBDAV_BIZPROC_WORKFLOW_ADMIN"], 
	"WEBDAV_BIZPROC_WORKFLOW_EDIT_URL" => $arResult["~PATH_TO_USER_FILES_WEBDAV_BIZPROC_WORKFLOW_EDIT"], 
	"WEBDAV_START_BIZPROC_URL" => $arResult["~PATH_TO_USER_FILES_WEBDAV_START_BIZPROC"], 
	"WEBDAV_TASK_LIST_URL" => $arResult["~PATH_TO_BIZPROC_TASK_LIST"], 
	"WEBDAV_TASK_URL" => $arResult["~PATH_TO_BIZPROC_TASK"], 
	
	"SET_TITLE"	=>	$arParams["SET_TITLE"],
	"STR_TITLE" => $arParams["STR_TITLE"], 
	"CACHE_TYPE"	=>	$arParams["CACHE_TYPE"],
	"CACHE_TIME"	=>	$arParams["CACHE_TIME"],
	"DISPLAY_PANEL"	=>	$arParams["DISPLAY_PANEL"]),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>
<?if (strtolower($arResult["VARIABLES"]["ACTION"]) == "clone"):?>
<script>
if (/*@cc_on ! @*/ false && new ActiveXObject("SharePoint.OpenDocuments.2"))
{
	BX.ready(
		function()	
		{
			setTimeout(
				function ()
				{
					try
					{
						var res = document.getElementsByTagName("A"); 
						for (var ii = 0; ii < res.length; ii++)
						{
							if (res[ii].className.indexOf("element-edit-office") >= 0) { res[ii].style.display = 'none'; }
						}
					}
					catch(e) {}
				}
				, 15
			)
		}
	);
}
</script>
<?
endif;
?>