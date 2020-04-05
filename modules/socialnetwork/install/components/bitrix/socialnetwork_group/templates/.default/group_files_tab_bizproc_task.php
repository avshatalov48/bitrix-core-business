<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$arCurFileInfo = pathinfo(__FILE__);
$langfile = trim(preg_replace("'[\\\\/]+'", "/", ($arCurFileInfo['dirname']."/lang/".LANGUAGE_ID."/".$arCurFileInfo['basename'])));
__IncludeLang($langfile);

$sTabName =  'tab_bizproc_view';

$sCurrentTab = (isset($_GET[$arParams["FORM_ID"].'_active_tab']) ? $_GET[$arParams["FORM_ID"].'_active_tab']: '');
$_GET[$arParams["FORM_ID"].'_active_tab'] = $sTabName;
ob_start();
$APPLICATION->IncludeComponent(
	"bitrix:bizproc.task", 
	"webdav.bizproc.task", 
	Array(
	"TASK_ID" => $arResult["VARIABLES"]["ID"],
	"USER_ID" => 0, 
	"WORKFLOW_ID" => "", 
    "DOWNLOAD_URL" => $arResult["URL_TEMPLATES"]["element_history_get"],
	"DOCUMENT_URL" =>  str_replace(
		array("#ELEMENT_ID#", "#ACTION#"), 
        array("#DOCUMENT_ID#", "EDIT"), $arResult["URL_TEMPLATES"]["element_edit"]),
	"SET_TITLE" => $arParams["SET_TITLE"],
	"SET_NAV_CHAIN" => $arParams["SET_TITLE"]),
	$component,
	array("HIDE_ICONS" => "Y")
);

$this->__component->arResult['TABS'][] = 
    array( "id" => $sTabName, 
    "name" => GetMessage("IBLIST_BP"), 
    "title" => GetMessage("IBLIST_BP"), 
    "fields" => array(
        array(  "id" => "IBLIST_BP", 
        "name" => GetMessage("IBLIST_BP"), 
        "colspan" => true,
        "type" => "custom", 
        "value" => ob_get_clean()
    )
) 
);

unset($_GET[$arParams["FORM_ID"].'_active_tab']);
if ($sCurrentTab !== '') 
    $_GET[$arParams["FORM_ID"].'_active_tab'] = $sCurrentTab;

?>
