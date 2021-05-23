<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?><?
$arCurFileInfo = pathinfo(__FILE__);
$langfile = trim(preg_replace("'[\\\\/]+'", "/", ($arCurFileInfo['dirname']."/lang/".LANGUAGE_ID."/".$arCurFileInfo['basename'])));
__IncludeLang($langfile);
$sTabName =  'tab_bizproc_view';

$sCurrentTab = (isset($_GET[$arParams["FORM_ID"].'_active_tab']) ? $_GET[$arParams["FORM_ID"].'_active_tab']: '');
$_GET[$arParams["FORM_ID"].'_active_tab'] = $sTabName;

ob_start();
$db_res = $arParams["OBJECT"]->_get_mixed_list(null, $arParams + array("SHOW_VERSION" => "Y"), $arResult["VARIABLES"]["ELEMENT_ID"]); 
if (!($db_res && $arResult["ELEMENT"] = $db_res->GetNext()))
{
	if ($arParams["SET_STATUS_404"] == "Y"):
		CHTTP::SetStatus("404 Not Found");
	endif;
    ob_end_clean();
	return 0;
}
elseif ($arParams["OBJECT"]->permission < "W")
{
    ob_end_clean();
	return 0;
}
elseif ($arParams["CHECK_CREATOR"] == "Y" && $arResult["ELEMENT"]["CREATED_BY"] != $GLOBALS['USER']->GetId())
{
	ShowError(GetMessage("WD_ACCESS_DENIED"));
    ob_end_clean();
	return 0;
}

$sBPListUrl = CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_USER_FILES_ELEMENT"], 
				array("user_id"=>$arResult["USER"]["ID"], "element_id" => $arResult["ELEMENT"]["ID"]));
$sBPListUrl = WDAddPageParams($sBPListUrl, array($arParams["FORM_ID"]."_active_tab"=>"tab_bizproc_view"));
?>
<ul class="bizproc-list bizproc-document-states">
    <li class="bizproc-list-item bizproc-document-start bizproc-list-item-first">
        <table class="bizproc-table-main">
            <tr>
                <td class="bizproc-field-name"><?=GetMessage("WD_BP_LOG")?></td>
                <td class="bizproc-field-value"><a href="<?=$sBPListUrl?>"><?=GetMessage("WD_BP_SHOWLIST")?></a></td>
            </tr>
        </table>
    </li>
<?
$APPLICATION->IncludeComponent("bitrix:bizproc.document", "webdav.bizproc.document", Array(
    "MODULE_ID"	=>	$arResult["VARIABLES"]["MODULE_ID"], 
    "ENTITY"	=>	$arResult["VARIABLES"]["ENTITY"], 
    "DOCUMENT_TYPE"	=>	$arResult["VARIABLES"]["DOCUMENT_TYPE"], 
    "DOCUMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
    "TASK_ID" => $arResult["VARIABLES"]["ID"],
    "WEBDAV_BIZPROC_VIEW_URL" => $arResult["URL_TEMPLATES"]["webdav_bizproc_view"],
    "TASK_EDIT_URL" => $arResult["~PATH_TO_BIZPROC_TASK"], 
    "TASK_LIST_URL" => $sBPListUrl,
    "WORKFLOW_LOG_URL" => str_replace("#ELEMENT_ID#", "#DOCUMENT_ID#", $arResult["~PATH_TO_USER_FILES_WEBDAV_BIZPROC_LOG"]), 
    "WORKFLOW_START_URL" => str_replace("#ELEMENT_ID#", "#DOCUMENT_ID#", $arResult["~PATH_TO_USER_FILES_WEBDAV_START_BIZPROC"]), 
    "USER_VIEW_URL" => $arResult["~PATH_TO_USER"],
    "SET_TITLE"	=>	"N"),
$component,
array("HIDE_ICONS" => "Y")
);
?>
    <li class="bizproc-list-item bizproc-list-item-first">
    <br />
<?
$APPLICATION->IncludeComponent("bitrix:bizproc.log", "webdav.bizproc.log", Array(
    "MODULE_ID" => $arResult["VARIABLES"]["MODULE_ID"], 
    "ENTITY" => $arResult["VARIABLES"]["ENTITY"], 
    "COMPONENT_VERSION" => 2,
    "DOCUMENT_TYPE" => $arResult["VARIABLES"]["DOCUMENT_TYPE"], 
    "DOCUMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"], 
    "ID" => $arResult["VARIABLES"]["ID"],
    "DOCUMENT_URL" => str_replace(
        array("#ELEMENT_ID#", "#WORKFLOW_ID#", "#ELEMENT_NAME#"), 
        array($arResult["VARIABLES"]["ELEMENT_ID"], "#WORKFLOW_ID#", "#NAME#"), $arResult["~PATH_TO_USER_FILES_WEBDAV_BIZPROC_HISTORY_GET"]),
    "USER_VIEW_URL" => $arResult["~PATH_TO_USER"],
    "SET_TITLE"	=>	$arParams["SET_TITLE"]),
    $component,
    array("HIDE_ICONS" => "Y")
);
?>
    </li>
</ul>
<?
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
