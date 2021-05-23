<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
$arCurFileInfo = pathinfo(__FILE__);
$langfile = trim(preg_replace("'[\\\\/]+'", "/", ($arCurFileInfo['dirname']."/lang/".LANGUAGE_ID."/".$arCurFileInfo['basename'])));
__IncludeLang($langfile);
$sTabName =  'tab_history';

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
    //ShowError(GetMessage("WD_ACCESS_DENIED"));
    return 0;
}
elseif ($arParams["CHECK_CREATOR"] == "Y" && $arResult["ELEMENT"]["CREATED_BY"] != $GLOBALS['USER']->GetId())
{
    //ShowError(GetMessage("WD_ACCESS_DENIED"));
    return 0;
}

$sCurrentTab = (isset($_GET[$arParams["FORM_ID"].'_active_tab']) ? $_GET[$arParams["FORM_ID"].'_active_tab']: '');
$_GET[$arParams["FORM_ID"].'_active_tab'] =$sTabName;

ob_start();
$result = $APPLICATION->IncludeComponent("bitrix:bizproc.document.history", "webdav", Array(
    "MODULE_ID" => $arResult["VARIABLES"]["MODULE_ID"],
    "ENTITY" => $arResult["VARIABLES"]["ENTITY"],
    "DOCUMENT_TYPE" => $arResult["VARIABLES"]["DOCUMENT_TYPE"],
    "DOCUMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
    "NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
    "OBJECT" => $arParams["OBJECT"], 
    "FORM_ID" => $arParams["FORM_ID"],
    "TAB_ID" => 'tab_history',
	"DOCUMENT_URL" => str_replace(
		array("#ELEMENT_ID#", "#WORKFLOW_ID#", "#ELEMENT_NAME#"), 
		array($arResult["VARIABLES"]["ELEMENT_ID"], "#ID#", "#NAME#"), $arResult["~PATH_TO_GROUP_FILES_WEBDAV_BIZPROC_HISTORY_GET"]),
    "SET_TITLE"	=> "N"),
$component,
array("HIDE_ICONS" => "Y")
);

if ($result !== false)
{
	$historyLength = (isset($this->__component->arResult["HISTORY_LENGTH"]) ? $this->__component->arResult["HISTORY_LENGTH"] : 0);
	$this->__component->arResult['TABS'][] = 
		array(
			"id" => $sTabName, 
			"name" => GetMessage("WD_HIST_ELEMENT_TITLE", array("#NUM#" => $historyLength)), 
			"title" => GetMessage("WD_HIST_ELEMENT"), 
			"fields" => array(
				array(
					"id" => "WD_HIST_ELEMENT", 
					"name" => GetMessage("WD_HIST_ELEMENT"), 
					"colspan" => true,
					"type" => "custom", 
					"value" => ob_get_clean()
				)
			) 
		);
}
else
{
	ob_get_clean();
}

unset($_GET[$arParams["FORM_ID"].'_active_tab']);
if ($sCurrentTab !== '') 
    $_GET[$arParams["FORM_ID"].'_active_tab'] = $sCurrentTab;
?>
