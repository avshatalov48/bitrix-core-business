<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
$sTplDir = trim(preg_replace("'[\\\\/]+'", "/", (__DIR__."/group_files_")));

if (empty($arParams["FORM_ID"]))
    $arParams["FORM_ID"] = "webdavForm".$arParams["FILES_GROUP_IBLOCK_ID"];

$arInfo = include($sTplDir."tab_edit.php");

if (!is_array($arInfo)) return; // error already shown

if ($arParams["WORKFLOW"] == "bizproc")
{ 
    include($sTplDir."tab_bizproc_history.php");
    include($sTplDir."tab_bizproc_document.php");
    include($sTplDir."tab_versions.php");
}
elseif ($arParams["WORKFLOW"] == "workflow")
{
    //include($sTplDir."tab_workflow_history.php");
}
else
{
    include($sTplDir."tab_bizproc_history.php");
}

include($sTplDir."tab_comments.php");

if ($arResult["VARIABLES"]["PERMISSION"] > "W")
	include($sTplDir."tab_permissions.php");

if (!$arParams["FORM_ID"]) $arParams["FORM_ID"] = "element";
$APPLICATION->IncludeComponent(
    "bitrix:main.interface.form",
    "",
    array(
        "FORM_ID" => $arParams["FORM_ID"],
        "SHOW_FORM_TAG" => "N",
        "TABS" => $this->__component->arResult['TABS'],
        "DATA" => $this->__component->arResult['DATA'],
		"SHOW_SETTINGS" => false
    ),
    ($this->__component->__parent ? $this->__component->__parent : $component)
);
?>
<script>
BX(function() {
	if (bxForm_<?=$arParams["FORM_ID"]?>) {
		if (expand_link = BX('bxForm_<?=$arParams["FORM_ID"]?>_expand_link')) {
			BX.hide(expand_link);
			if (!!expand_link.nextElementSibling)
				BX.hide(expand_link.nextElementSibling);
		}
	}
});
</script>
