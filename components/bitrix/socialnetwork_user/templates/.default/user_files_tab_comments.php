<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
$arCurFileInfo = pathinfo(__FILE__);
$langfile = trim(preg_replace("'[\\\\/]+'", "/", ($arCurFileInfo['dirname']."/lang/".LANGUAGE_ID."/".$arCurFileInfo['basename'])));
__IncludeLang($langfile);
$sTabName =  "tab_comments";

if(is_array($arInfo) && $arInfo["ELEMENT_ID"] && $arParams["FILES_USE_COMMENTS"]=="Y" && IsModuleInstalled("forum"))
{
    $bShowHide = (false /* (intval($arInfo["ELEMENT"]["PROPERTIES"]["FORUM_TOPIC_ID"]["VALUE"]) <= 0 /*&& 	
        ($arParams['WORKFLOW'] == "bizproc" && $arInfo["ELEMENT"]["BP_PUBLISHED"] != "Y" || 
       $arParams['WORKFLOW'] == "workflow" && (!(intval($arInfo["ELEMENT"]["WF_STATUS_ID"]) == 1 && intval($arInfo["ELEMENT"]["WF_PARENT_ELEMENT_ID"]) <= 0))) */);
    if (!$bShowHide)
    {
        if ($arInfo["ELEMENT"]["PROPERTY_FORUM_MESSAGE_CNT_VALUE"] == null)
            $arInfo["ELEMENT"]["PROPERTY_FORUM_MESSAGE_CNT_VALUE"] = 0;

        $sCurrentTab = (isset($_GET[$arParams["FORM_ID"].'_active_tab']) ? $_GET[$arParams["FORM_ID"].'_active_tab'] : '');
        $_GET[$arParams["FORM_ID"].'_active_tab'] = $sTabName;

		if (! (isset($_REQUEST['AJAX_CALL']) || isset($_REQUEST['save_product_review'])) ) 
			ob_start();

    ?><a name="reviews"></a><?
        $msgCount = $APPLICATION->IncludeComponent(
        "bitrix:forum.topic.reviews",
        ".default",
        Array(
            "IBLOCK_TYPE"	=>	$arParams["FILES_USER_IBLOCK_TYPE"],
            "IBLOCK_ID"	=>	$arParams["FILES_USER_IBLOCK_ID"],
            "FORUM_ID" => $arParams["FILES_FORUM_ID"],
            "ELEMENT_ID" => $arInfo["ELEMENT_ID"],
            "ENABLE_HIDDEN" => "Y",
            
            "URL_TEMPLATES_READ" => "",
            "URL_TEMPLATES_PROFILE_VIEW" => str_replace("#USER_ID#", "#UID#", $arResult["~PATH_TO_USER"]),
            "URL_TEMPLATES_DETAIL" => $arResult["~PATH_TO_USER_FILES_ELEMENT"],

			"SHOW_MINIMIZED" => "Y",
            "POST_FIRST_MESSAGE" => "Y", 
            "POST_FIRST_MESSAGE_TEMPLATE" => GetMessage("WD_TEMPLATE_MESSAGE"), 
            "SUBSCRIBE_AUTHOR_ELEMENT" => "Y", 
            "IMAGE_SIZE" => false, 
            "MESSAGES_PER_PAGE" => 20,
            "DATE_TIME_FORMAT" => false, 
            "USE_CAPTCHA" => $arParams["FILES_USE_CAPTCHA"],
            "PREORDER" => ($arParams["FILES_PREORDER"] == "N" ? "N" : "Y"),
            "PAGE_NAVIGATION_TEMPLATE" => false, 
            "DISPLAY_PANEL" => "N", 
				"SHOW_RATING" => $arParams["SHOW_RATING"],
				"RATING_TYPE" => $arParams["RATING_TYPE"],
				"PATH_TO_USER" => $arParams["PATH_TO_USER"],
            "CACHE_TYPE" => $arParams["CACHE_TYPE"],
            "CACHE_TIME" => $arParams["CACHE_TIME"],

            "PATH_TO_SMILE" => $arParams["FILES_PATH_TO_SMILE"],
            "SHOW_LINK_TO_FORUM" => "N",
        ),
        $component,
        array("HIDE_ICONS" => "Y")
    );

	if (isset($_REQUEST['AJAX_CALL']) || isset($_REQUEST['save_product_review']))
		return;
?>
	<script>
		function wdUpdateCommentTabTitle(result)
		{
			var tab = BX('tab_tab_comments');
			if (tab)
				tab.innerHTML = "<?=CUtil::JSEscape(GetMessage('WD_COMMENTS_NAME_JS'))?>";

			var formID = "<?=CUtil::JSEscape($arParams['FORM_ID'])?>";
			var curpage = top.window.location.href;
			if (curpage.indexOf(formID) > -1)
				curpage = curpage.replace(new RegExp(formID+"\\_active\\_tab\\=(\\w+)", "gi"), '');
			curpage += ((curpage.indexOf('?') == -1) ? '?' : '&');
			curpage += formID+'_active_tab=<?=CUtil::JSEscape($sTabName);?>';
			window.curpage = curpage;
		}

		BX.addCustomEvent('onForumCommentAJAXAction', wdUpdateCommentTabTitle);
		BX.addCustomEvent('onForumCommentAJAXPost', wdUpdateCommentTabTitle);
	</script>
<?
	if ($msgCount !== false) {
		if ($msgCount < 2) 
		{
?>
		<script> BX(function() {setTimeout(function(){if (typeof replyForumFormOpen == 'function') {replyForumFormOpen();}}, 300);}); </script>
<?
		}
	} else {
		ShowError(GetMessage("WD_ACCESS_DENIED"));
	}
    $this->__component->arResult['TABS'][] = 
        array( "id" => $sTabName, 
               "name" => GetMessage("WD_COMMENTS_NAME" , array("#NUM#" => ($msgCount > 1 ? $msgCount-1 : '0'))), 
               "title" => GetMessage("WD_COMMENTS_TITLE"), 
               "fields" => array(
                   array(  "id" => "WD_ELEMENT_COMMENTS", 
                            "name" => GetMessage("WD_COMMENTS_NAME"), 
                            "colspan" => true,
                            "type" => "custom", 
                            "value" => ob_get_clean()
                        )
                )
        );

    unset($_GET[$arParams["FORM_ID"].'_active_tab']);
    if (!empty($sCurrentTab))
        $_GET[$arParams["FORM_ID"].'_active_tab'] = $sCurrentTab;
    }
}
?>
