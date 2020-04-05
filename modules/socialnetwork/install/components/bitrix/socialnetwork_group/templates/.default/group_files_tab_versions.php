<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if ($arResult["VARIABLES"]["PERMISSION"] < "U")
	return false;

$arCurFileInfo = pathinfo(__FILE__);
$langfile = trim(preg_replace("'[\\\\/]+'", "/", ($arCurFileInfo['dirname']."/lang/".LANGUAGE_ID."/".$arCurFileInfo['basename'])));
__IncludeLang($langfile);

    if (!isset($arInfo["ELEMENT"]["ORIGINAL"]) || empty($arInfo["ELEMENT"]["ORIGINAL"]))
    {

        $sCurrentTab = (isset($_GET[$arParams["FORM_ID"].'_active_tab']) ? $_GET[$arParams["FORM_ID"].'_active_tab']: '');
        $_GET[$arParams["FORM_ID"].'_active_tab'] = 'tab_version';

        ob_start();
        $APPLICATION->IncludeComponent("bitrix:webdav.element.version", ".default", Array(
            "OBJECT"	=>	$arParams["OBJECT"], 
            "IBLOCK_TYPE"	=>	$arParams["FILES_GROUP_IBLOCK_TYPE"],
            "IBLOCK_ID"	=>	$arParams["FILES_GROUP_IBLOCK_ID"],
            "ROOT_SECTION_ID"	=>	$arResult["VARIABLES"]["ROOT_SECTION_ID"],
            "ELEMENT_ID"	=>	$arResult["VARIABLES"]["ELEMENT_ID"],
            "NAME_FILE_PROPERTY"	=>	$arParams["NAME_FILE_PROPERTY"],
            "PERMISSION"	=>	$arResult["VARIABLES"]["PERMISSION"],
            "CHECK_CREATOR"	=>	$arResult["VARIABLES"]["CHECK_CREATOR"],
                    
            "SECTIONS_URL" => $arResult["~PATH_TO_GROUP_FILES_SHORT"],
            "SECTION_EDIT_URL" => $arResult["~PATH_TO_GROUP_FILES_SECTION_EDIT"],
            "ELEMENT_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT"],
            "ELEMENT_EDIT_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_VERSION"],
            "ELEMENT_FILE_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_FILE"],
            "ELEMENT_HISTORY_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_HISTORY"],
            "ELEMENT_HISTORY_GET_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_HISTORY_GET"],
            "ELEMENT_VERSION_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_VERSION"],
            "ELEMENT_VERSIONS_URL" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_VERSIONS"],
            "ELEMENT_UPLOAD" => $arResult["~PATH_TO_GROUP_FILES_ELEMENT_UPLOAD"],
            "HELP_URL" => $arResult["~PATH_TO_GROUP_FILES_HELP"],
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
                "FORM_ID" => $arParams["FORM_ID"],
                "TAB_ID" => 'tab_version',

                "SET_NAV_CHAIN"	=>	"N",
                "SET_TITLE"	=>	"N",
            "STR_TITLE" => $arParams["STR_TITLE"], 
            "SHOW_WEBDAV" => $arParams["SHOW_WEBDAV"], 
                    
            "CACHE_TYPE"	=>	$arParams["CACHE_TYPE"],
            "CACHE_TIME"	=>	$arParams["CACHE_TIME"],
            "DISPLAY_PANEL"	=>	$arParams["DISPLAY_PANEL"]),
            $component,
            array("HIDE_ICONS" => "Y")
        );

        $this->__component->arResult['TABS'][] = 
            array( "id" => "tab_version", 
                   "name" => GetMessage("WD_VERSIONS"), 
                   "title" => GetMessage("WD_EV_TITLE"), 
                   "fields" => array(
                       array(  "id" => "WD_VERSIONS", 
                                "name" => GetMessage("WD_VERSIONS"), 
                                "colspan" => true,
                                "type" => "custom", 
                                "value" => ob_get_clean()
                            )
                    ) 
            );

        unset($_GET[$arParams["FORM_ID"].'_active_tab']);
        if ($sCurrentTab !== ''):
            $_GET[$arParams["FORM_ID"].'_active_tab'] = $sCurrentTab;
        endif;
    }
?>
