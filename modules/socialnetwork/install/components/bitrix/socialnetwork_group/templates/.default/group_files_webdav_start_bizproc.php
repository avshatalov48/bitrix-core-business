<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$sTplDir = trim(preg_replace("'[\\\\/]+'", "/", (__DIR__."/group_files_")));
if (empty($arParams["FORM_ID"]))
    $arParams["FORM_ID"] = "webdavForm".$arParams["FILES_GROUP_IBLOCK_ID"];

$arInfo = include($sTplDir."tab_edit.php");

if (!is_array($arInfo)) return; // error already shown

if ($arParams["WORKFLOW"] == "bizproc")
{ 
    include($sTplDir."tab_bizproc_history.php");
    //include($sTplDir."tab_bizproc_document.php");
    include($sTplDir."tab_bizproc_start.php");
    include($sTplDir."tab_versions.php");
}
elseif ($arParams["WORKFLOW"] == "workflow")
{
    include($sTplDir."tab_workflow_history.php");
}
else
{
    include($sTplDir."tab_bizproc_history.php");
}

include($sTplDir."tab_comments.php");

if (!isset($_GET[$arParams["FORM_ID"].'_active_tab']))
    $_REQUEST[$arParams["FORM_ID"].'_active_tab'] = "tab_bizproc_view";

if (!$arParams["FORM_ID"]) $arParams["FORM_ID"] = "element";
$APPLICATION->IncludeComponent(
    "bitrix:main.interface.form",
    "",
    array(
        "FORM_ID" => $arParams["FORM_ID"],
        "TABS" => $this->__component->arResult['TABS'],
        "DATA" => $this->__component->arResult['DATA'],
    ),
    ($this->__component->__parent ? $this->__component->__parent : $component)
); 
?>
