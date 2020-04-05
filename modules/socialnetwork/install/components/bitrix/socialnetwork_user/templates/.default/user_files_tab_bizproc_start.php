<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?><?
$arCurFileInfo = pathinfo(__FILE__);
$langfile = trim(preg_replace("'[\\\\/]+'", "/", ($arCurFileInfo['dirname']."/lang/".LANGUAGE_ID."/".$arCurFileInfo['basename'])));
__IncludeLang($langfile);
$sTabName =  'tab_bizproc_view';

$sCurrentTab = (isset($_GET[$arParams["FORM_ID"].'_active_tab']) ? $_GET[$arParams["FORM_ID"].'_active_tab']: '');
$_GET[$arParams["FORM_ID"].'_active_tab'] = $sTabName;

ob_start();
$APPLICATION->IncludeComponent("bitrix:bizproc.workflow.start", "", Array(
	"MODULE_ID" => $arResult["VARIABLES"]["MODULE_ID"],
	"ENTITY" => $arResult["VARIABLES"]["ENTITY"],
	"DOCUMENT_TYPE" => $arResult["VARIABLES"]["DOCUMENT_TYPE"], 
	"DOCUMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
	"TEMPLATE_ID" => $arResult["VARIABLES"]["TEMPLATE_ID"], 
	"SET_TITLE"	=>	$arParams["SET_TITLE"]),
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
