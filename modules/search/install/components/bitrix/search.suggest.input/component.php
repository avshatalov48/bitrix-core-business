<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentName */
/** @var string $componentPath */
/** @var string $componentTemplate */
/** @var string $parentComponentName */
/** @var string $parentComponentPath */
/** @var string $parentComponentTemplate */

if(!CModule::IncludeModule("search"))
{
	ShowError(GetMessage("CC_BSSI_MODULE_NOT_INSTALLED"));
	return;
}

$arParams["NAME"] = trim($arParams["NAME"]);
if(!strlen($arParams["NAME"]))
	$arParams["~NAME"] = $arParams["NAME"] = "q";

$arParams["INPUT_SIZE"] = intval($arParams["INPUT_SIZE"]);
if(!$arParams["INPUT_SIZE"])
	$arParams["~INPUT_SIZE"] = $arParams["INPUT_SIZE"] = 40;

$arParams["DROPDOWN_SIZE"] = intval($arParams["DROPDOWN_SIZE"]);
if(!$arParams["DROPDOWN_SIZE"])
	$arParams["~DROPDOWN_SIZE"] = $arParams["DROPDOWN_SIZE"] = 10;

$arResult["ID"] = preg_replace("/\\W/", "_", $arParams["NAME"]).$this->randString();

$arResult["~ADDITIONAL_VALUES"] = "pe:".$arParams["DROPDOWN_SIZE"].",md5:".$arParams["FILTER_MD5"].",site:".SITE_ID;
$arResult["ADDITIONAL_VALUES"] = CUtil::JSEscape($arResult["~ADDITIONAL_VALUES"]);

$this->IncludeComponentTemplate();
?>