<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (IntVal($arParams["PAGE_ELEMENTS"]) <= 0)
	$arParams["PAGE_ELEMENTS"] = 10;

$signer = new \Bitrix\Main\Security\Sign\Signer;
	
$arParams["ADDITIONAL_VALUES"] = "pe:".$arParams["PAGE_ELEMENTS"].",";
$arParams["ADDITIONAL_VALUES"] .= "gf:".$arParams["GROUP_ID"].",";

$nt = str_replace(",", "#COMMA#", $arParams["NAME_TEMPLATE"]);
$arParams["ADDITIONAL_VALUES"] .= "nt:".$signer->sign($nt).",";

$sl = $arParams["SHOW_LOGIN"];
$arParams["ADDITIONAL_VALUES"] .= "sl:".$signer->sign($sl).",";

if (IsModuleInstalled("extranet") && strlen(CExtranet::GetExtranetSiteID()) > 0)
{
	$arParams["ADDITIONAL_VALUES"] .= "ex:".$arParams["EXTRANET"].",";
	$arParams["ADDITIONAL_VALUES"] .= "site:".SITE_ID."";
}

$arParams["~ADDITIONAL_VALUES"] = $arParams["ADDITIONAL_VALUES"];
$arParams["ADDITIONAL_VALUES"] = CUtil::JSEscape($arParams["ADDITIONAL_VALUES"]);

$arResult["TEXT"] = str_replace(array("<", ">"), array('&lt;', '&gt;'), $arParams["~TEXT"]);
?>