<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
CUtil::InitJSCore(array("ajax"));
/********************************************************************
				Input params
 ********************************************************************/
/***************** BASE ********************************************/
if (!isset($arParams["ATTACH_MODE"]))
{
	if (intVal($arParams["IMAGE_SIZE"]) > 0)
	{
		$arParams["ATTACH_MODE"] = array("THUMB", "NAME");
		$arParams["ATTACH_SIZE"] = $arParams["IMAGE_SIZE"];
	}
	else
	{
		$arParams["ATTACH_MODE"] = array("NAME");
		$arParams["ATTACH_SIZE"] = 0;
	}
}
$arParams["ATTACH_MODE"] = (is_array($arParams["ATTACH_MODE"]) ? $arParams["ATTACH_MODE"] : array());
$arParams["ATTACH_MODE"] = (!in_array("NAME", $arParams["ATTACH_MODE"]) && !in_array("THUMB", $arParams["ATTACH_MODE"]) ? array("NAME") : $arParams["ATTACH_MODE"]);
$arParams["ATTACH_SIZE"] = intVal(intVal($arParams["ATTACH_SIZE"]) > 0 ? $arParams["ATTACH_SIZE"] : 90);

$arParams["SHOW_MAIL"] = (($arParams["SEND_MAIL"] <= "A" || ($arParams["SEND_MAIL"] <= "E" && !$GLOBALS['USER']->IsAuthorized())) ? "N" : "Y");
$arParams["SHOW_ICQ"] = ($arParams["SHOW_ICQ"] == "Y" ? "Y" : "N");
$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] == "Y" ? "Y" : "N");

$arParams["AJAX_TYPE"] = "N";

$arParams["SHOW_VOTE"] = "N";
$arParams["VOTE_TEMPLATE"] = "";
$arParams["SEO_USER"] = $arParams["SEO_USER"];
/********************************************************************
				/Input params
 ********************************************************************/
?>