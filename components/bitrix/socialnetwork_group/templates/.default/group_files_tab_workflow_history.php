<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
$arCurFileInfo = pathinfo(__FILE__);
$langfile = trim(preg_replace("'[\\\\/]+'", "/", ($arCurFileInfo['dirname']."/lang/".LANGUAGE_ID."/".$arCurFileInfo['basename'])));
__IncludeLang($langfile);

    $sCurrentTab = (isset($_GET[$arParams["FORM_ID"].'_active_tab']) ? $_GET[$arParams["FORM_ID"].'_active_tab']: '');
    $_GET[$arParams["FORM_ID"].'_active_tab'] = 'tab_history';

    ob_start();
    $APPLICATION->IncludeComponent("bitrix:webdav.element.hist", ".default", Array(
        "IBLOCK_TYPE"   =>  $arParams["IBLOCK_TYPE"],
        "IBLOCK_ID" =>  $arParams["IBLOCK_ID"],
        "ELEMENT_ID"    =>  $arResult["VARIABLES"]["ELEMENT_ID"],
        "OBJECT"        => $arParams["OBJECT"],
        "CONVERT"   =>  $arParams["CONVERT"],
        "PERMISSION" => $arParams["PERMISSION"], 
        "CHECK_CREATOR" => $arParams["CHECK_CREATOR"],
        "FORM_ID" => $arParams["FORM_ID"],
        "TAB_ID" => 'tab_history',

        "SECTIONS_URL" => $arResult["URL_TEMPLATES"]["sections"],
        "ELEMENT_URL" => $arResult["URL_TEMPLATES"]["element"],
        "ELEMENT_EDIT_URL" => $arResult["URL_TEMPLATES"]["element_edit"],
        "ELEMENT_HISTORY_URL" => $arResult["URL_TEMPLATES"]["element_history"],
        "ELEMENT_HISTORY_GET_URL" => $arResult["URL_TEMPLATES"]["element_history_get"],
        "USER_VIEW_URL" => $arResult["URL_TEMPLATES"]["user_view"],

        "PAGE_ELEMENTS" =>  $arParams["PAGE_ELEMENTS"],
        "PAGE_NAVIGATION_TEMPLATE"  =>  $arParams["PAGE_NAVIGATION_TEMPLATE"],

        "SET_NAV_CHAIN" => "N",
        "SET_TITLE" => "N",
        "STR_TITLE" => "N",
        "CACHE_TYPE"    =>  $arParams["CACHE_TYPE"],
        "CACHE_TIME"    =>  $arParams["CACHE_TIME"],
        "DISPLAY_PANEL" =>  $arParams["DISPLAY_PANEL"]),
    $component,
    array("HIDE_ICONS" => "Y")
    );

    $historyLength = (isset($this->__component->arResult["HISTORY_LENGTH"]) ? $this->__component->arResult["HISTORY_LENGTH"] : 0);
    $this->__component->arResult['TABS'][] = 
        array( "id" => "tab_history", 
               "name" => GetMessage("WD_HIST_ELEMENT_TITLE", array("#NUM#" => $historyLength)), 
               "title" => GetMessage("WD_HIST_ELEMENT"), 
               "fields" => array(
                   array(  "id" => "WD_HIST_ELEMENT", 
                            "name" => GetMessage("WD_HIST_ELEMENT"), 
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
?>
