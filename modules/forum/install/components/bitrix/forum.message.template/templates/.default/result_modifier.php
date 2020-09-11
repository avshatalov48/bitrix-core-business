<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
/********************************************************************
				Input params
	********************************************************************/
if (!empty($arParams["~MESSAGE"]))
{
	$arParams["~MESSAGE"]["CHECKED"] = ($arParams["~MESSAGE"]["CHECKED"] === true || $arParams["~MESSAGE"]["CHECKED"] == "Y" ? "Y" : "N");
	$arParams["~MESSAGE"]["FILES_PARSED"] = (is_array($arParams["~MESSAGE"]["FILES_PARSED"]) ? $arParams["~MESSAGE"]["FILES_PARSED"] : array($arParams["~MESSAGE"]["FILES_PARSED"]));
}

//$arParams["~MESSAGE"]["NUMBER"] = (empty($arParams["~MESSAGE"]["NUMBER"]) ? $arParams["~MESSAGE"]["ID"] : $arParams["~MESSAGE"]["NUMBER"]);

$arParams["ATTACH_MODE"] = (is_array($arParams["ATTACH_MODE"]) ? $arParams["ATTACH_MODE"] : array());
$arParams["ATTACH_MODE"] = (!in_array("NAME", $arParams["ATTACH_MODE"]) && !in_array("THUMB", $arParams["ATTACH_MODE"]) ? array("NAME") : $arParams["ATTACH_MODE"]);
$arParams["ATTACH_MODE"] = (in_array("NAME", $arParams["ATTACH_MODE"]) && in_array("THUMB", $arParams["ATTACH_MODE"]) ? "FULL" :
	(in_array("THUMB", $arParams["ATTACH_MODE"]) ? "THUMB" : "LINK"));
$arParams["ATTACH_SIZE"] = intval(intVal($arParams["ATTACH_SIZE"]) > 0 ? $arParams["ATTACH_SIZE"] : 90);

$arParams["SHOW_RATING"] = ($arParams["SHOW_RATING"] == "Y" ? "Y" : "N");
$arParams["RATING_ID"] = (is_array($arParams["RATING_ID"]) ? $arParams["RATING_ID"] : array($arParams["RATING_ID"]));

/*
* $arRes["USER"]["PERMISSION"] >= "Q"
* $arRes["TOPIC"]["APPROVED"] != "Y"
* $arRes["USER"]["RIGHTS"]["ADD_MESSAGE"] == "Y"
* $arRes["FORUM"]["ALLOW_QUOTE"] == "Y"
*/
if (is_array($arParams["~arParams"]))
	$arParams += $arParams["~arParams"]; // $arParams form main component
/*
* $arParams["URL_TEMPLATES_PROFILE_VIEW"]
* $arParams["SHOW_MAIL"]
* $arParams["SHOW_ICQ"]
* $arParams['AJAX_POST']
* $arParams["SHOW_NAME_LINK"]
*/

$arParams["SHOW_NAME_LINK"] = ($arParams["SHOW_NAME_LINK"] == "N" ? "N" : "Y");
$arParams["SHOW_PM"] = (intval(COption::GetOptionString("forum", "UsePMVersion", "2")) > 0 && $GLOBALS["USER"]->IsAuthorized() ? "Y" : "N");
$arParams["SHOW_MAIL"] = ($arParams["SHOW_MAIL"] == "Y" ? "Y" : "N");
$arParams["SEO_USER"] = (in_array($arParams["SEO_USER"], array("Y", "N", "TEXT")) ? $arParams["SEO_USER"] : "Y");
$arParams["USER_TMPL"] = '<noindex><a rel="nofollow" href="#URL#" title="'.GetMessage("F_USER_PROFILE").'">#NAME#</a></noindex>';
if ($arParams["SEO_USER"] == "N") $arParams["USER_TMPL"] = '<a href="#URL#" title="'.GetMessage("F_USER_PROFILE").'">#NAME#</a>';
elseif ($arParams["SEO_USER"] == "TEXT") $arParams["USER_TMPL"] = '#NAME#';
/********************************************************************
				/Input params
 ********************************************************************/
?>