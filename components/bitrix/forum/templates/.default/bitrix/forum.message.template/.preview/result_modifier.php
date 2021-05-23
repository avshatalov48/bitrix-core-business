<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
/********************************************************************
				Input params
	********************************************************************/
$arParams["~MESSAGE"]["FILES"] = (!is_array($arParams["~MESSAGE"]["FILES"]) ? array() : $arParams["~MESSAGE"]["FILES"]);
$arParams["~MESSAGE"]["FILES_PARSED"] = (!is_array($arParams["~MESSAGE"]["FILES_PARSED"]) ? array() : $arParams["~MESSAGE"]["FILES_PARSED"]);
$arParams["ATTACH_MODE"] = (is_array($arParams["ATTACH_MODE"]) ? $arParams["ATTACH_MODE"] : array());
$arParams["ATTACH_MODE"] = (!in_array("NAME", $arParams["ATTACH_MODE"]) && !in_array("THUMB", $arParams["ATTACH_MODE"]) ? array("NAME") : $arParams["ATTACH_MODE"]);
$arParams["ATTACH_MODE"] = (in_array("NAME", $arParams["ATTACH_MODE"]) && in_array("THUMB", $arParams["ATTACH_MODE"]) ? "FULL" :
	(in_array("THUMB", $arParams["ATTACH_MODE"]) ? "THUMB" : "LINK"));
$arParams["ATTACH_SIZE"] = intval(intVal($arParams["ATTACH_SIZE"]) > 0 ? $arParams["ATTACH_SIZE"] : 90);
/********************************************************************
				/Input params
 ********************************************************************/
?>