<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["PATH_TO_ICON"] = "";

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
$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] == "Y" ? "Y" : "N");

$arParams["AJAX_TYPE"] = "N";

$arParams["SHOW_VOTE"] = "N";
$arParams["VOTE_TEMPLATE"] = "";
$arParams["SEO_USER"] = $arParams["SEO_USER"];
/********************************************************************
				/Input params
********************************************************************/

if (!function_exists("__array_stretch"))
{
	function __array_stretch($arGroup, $depth = 0)
	{
		$arResult = array();
		
		if (intval($arGroup["ID"]) > 0)
		{
			$arResult["GROUP_".$arGroup["ID"]] = $arGroup;
			unset($arResult["GROUP_".$arGroup["ID"]]["GROUPS"]);
			unset($arResult["GROUP_".$arGroup["ID"]]["FORUM"]);
			$arResult["GROUP_".$arGroup["ID"]]["DEPTH"] = $depth; 
			$arResult["GROUP_".$arGroup["ID"]]["TYPE"] = "GROUP"; 
		}
		if (array_key_exists("FORUMS", $arGroup))
		{
			foreach ($arGroup["FORUMS"] as $res)
			{
				$arResult["FORUM_".$res["ID"]] = $res; 
				$arResult["FORUM_".$res["ID"]]["DEPTH"] = $depth; 
				$arResult["FORUM_".$res["ID"]]["TYPE"] = "FORUM"; 
			}
		}
				
		if (array_key_exists("GROUPS", $arGroup))
		{
			$depth++;
			foreach ($arGroup["GROUPS"] as $key => $val)
			{
				$res = __array_stretch($arGroup["GROUPS"][$key], $depth);
				$arResult = array_merge($arResult, $res);
			}
		}
		return $arResult;
	}
}
$arResult["GROUPS_FORUMS"] = __array_stretch($arResult["GROUPS_FORUMS"]);
?>