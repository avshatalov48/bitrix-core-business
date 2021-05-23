<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if ($arParams['AJAX_POST']=='Y' && in_array($arParams['ACTION'], array('REPLY', 'VIEW')))
	ob_start();
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
if (!isset($arParams["ATTACH_MODE"]))
{
	if (intval($arParams["IMAGE_SIZE"]) > 0)
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
$arParams["ATTACH_SIZE"] = intval(intVal($arParams["ATTACH_SIZE"]) > 0 ? $arParams["ATTACH_SIZE"] : 90);

$arParams["SHOW_MAIL"] = (($arParams["SEND_MAIL"] <= "A" || ($arParams["SEND_MAIL"] <= "E" && !$GLOBALS['USER']->IsAuthorized())) ? "N" : "Y");
$arParams["SHOW_ICQ"] = ($arParams["SHOW_ICQ"] == "Y" ? "Y" : "N");

$arParams["AJAX_TYPE"] = ($arParams["AJAX_TYPE"] == "Y" ? "Y" : "N");
$arParams["SHOW_RSS"] = ($arParams["SHOW_RSS"] == "N" ? "N" : "Y");
$arParams["SHOW_FIRST_POST"] = ($arParams["SHOW_FIRST_POST"] == "Y" ? "Y" : "N");
if ($arParams["SHOW_RSS"] == "Y"):
	$arParams["SHOW_RSS"] = (!$USER->IsAuthorized() || CForumNew::GetUserPermission($arParams["FID"], array(2)) > "A") ? "Y" : "N";
endif;
$arParams["SHOW_NAME_LINK"] = ($arParams["SHOW_NAME_LINK"] == "N" ? "N" : "Y");

$arParams["SHOW_VOTE"] = ($arParams["SHOW_VOTE"] == "Y" ? "Y" : "N");
$arParams["VOTE_TEMPLATE"] = (trim($arParams["VOTE_TEMPLATE"]) <> '' ? trim($arParams["VOTE_TEMPLATE"]) : "light");
$arParams["SEO_USER"] = $arParams["SEO_USER"];
/********************************************************************
				/Input params
********************************************************************/
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/js/main/utils.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/forum.interface/templates/.default/script.js");

if ($arParams["SHOW_RSS"] == "Y"):
	$APPLICATION->AddHeadString('<link rel="alternate" type="application/rss+xml" href="'.$arResult["URL"]["RSS_DEFAULT"].'" />');
endif;
CUtil::InitJSCore(array("ajax", "fx"));
?>