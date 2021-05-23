<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$arCurFileInfo = pathinfo(__FILE__);
$langfile = trim(preg_replace("'[\\\\/]+'", "/", ($arCurFileInfo['dirname']."/lang/".LANGUAGE_ID."/".$arCurFileInfo['basename'])));
__IncludeLang($langfile);

    $sTabName =  'tab_bizproc_view';
    if(is_array($arInfo) && $arInfo["ELEMENT_ID"]):
        $sCurrentTab = (isset($_GET[$arParams["FORM_ID"].'_active_tab']) ? $_GET[$arParams["FORM_ID"].'_active_tab']: '');
        $_GET[$arParams["FORM_ID"].'_active_tab'] = $sTabName;

        ob_start();
        $result = $APPLICATION->IncludeComponent("bitrix:bizproc.document", "webdav.bizproc.document", Array(
            "MODULE_ID"	=>	$arResult["VARIABLES"]["MODULE_ID"], 
            "ENTITY"	=>	$arResult["VARIABLES"]["ENTITY"], 
            "DOCUMENT_TYPE"	=>	$arResult["VARIABLES"]["DOCUMENT_TYPE"], 
            "DOCUMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
			"WEBDAV_BIZPROC_VIEW_URL" => $arResult["URL_TEMPLATES"]["webdav_bizproc_view"],
            "TASK_EDIT_URL" => $arResult["~PATH_TO_BIZPROC_TASK"], 
            "WORKFLOW_LOG_URL" => str_replace("#ELEMENT_ID#", "#DOCUMENT_ID#", $arResult["~PATH_TO_USER_FILES_WEBDAV_BIZPROC_LOG"]), 
            "WORKFLOW_START_URL" => str_replace("#ELEMENT_ID#", "#DOCUMENT_ID#", $arResult["~PATH_TO_USER_FILES_WEBDAV_START_BIZPROC"]), 
            "USER_VIEW_URL" => $arResult["~PATH_TO_USER"],
            "SET_TITLE"	=>	"N"),
            $component,
            array("HIDE_ICONS" => "Y")
        );

		if ($result !== false)
		{
			$this->__component->arResult['TABS'][] = 
				array(
					"id" => $sTabName, 
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
		}
		else
		{
			ob_get_clean();
		}
        unset($_GET[$arParams["FORM_ID"].'_active_tab']);
        if ($sCurrentTab !== '') 
            $_GET[$arParams["FORM_ID"].'_active_tab'] = $sCurrentTab;
    endif;
?>
