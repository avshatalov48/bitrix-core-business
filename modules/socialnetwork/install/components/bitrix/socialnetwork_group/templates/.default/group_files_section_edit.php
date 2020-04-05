<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$sTplDir = trim(preg_replace("'[\\\\/]+'", "/", (dirname(__FILE__)."/group_files_")));

if (empty($arParams["FORM_ID"]))
    $arParams["FORM_ID"] = "webdavForm".$arParams["FILES_GROUP_IBLOCK_ID"];

include($sTplDir."tab_section.php");

if ($arResult["VARIABLES"]["PERMISSION"] > "W")
	include($sTplDir."tab_permissions.php");

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
