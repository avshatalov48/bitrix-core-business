<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// ******************************************************************
// ******************************************************************
	if (intVal($arParams["PAGE_ELEMENTS"]) <= 0)
		$arParams["PAGE_ELEMENTS"] = 10;
	$arParams["SORT"] = "name";
	if ($arParams["SORT_BY_CNT"] == "Y")
		$arParams["SORT"] = "cnt";

	$arParams["ADDITIONAL_VALUES"] = "pe:".$arParams["PAGE_ELEMENTS"].",sort:".$arParams["SORT"]."";
	if (!empty($arResult["exFILTER"]["MODULE_ID"]))
		$arParams["ADDITIONAL_VALUES"] .= ",mid:".$arResult["exFILTER"]["MODULE_ID"];
	if (!empty($arResult["exFILTER"]["PARAM1"]))
		$arParams["ADDITIONAL_VALUES"] .= ",pm1:".$arResult["exFILTER"]["PARAM1"];
	if (!empty($arResult["exFILTER"]["PARAM2"]))
		$arParams["ADDITIONAL_VALUES"] .= ",pm2:".$arResult["exFILTER"]["PARAM2"];
	if (!empty($arResult["exFILTER"]["SOCIAL_NETWORK_GROUP"]))
		$arParams["ADDITIONAL_VALUES"] .= ",sng:".$arResult["exFILTER"]["SOCIAL_NETWORK_GROUP"];
	if (!empty($arResult["exFILTER"]["SITE_ID"]))
		$arParams["ADDITIONAL_VALUES"] .= ",site_id:".$arResult["exFILTER"]["SITE_ID"];
	$arParams["~ADDITIONAL_VALUES"] = $arParams["ADDITIONAL_VALUES"];
	$arParams["ADDITIONAL_VALUES"] = CUtil::JSEscape($arParams["ADDITIONAL_VALUES"]);

	$arResult["TEXT"] = str_replace(array("<", ">"), array('&lt;', '&gt;'), $arParams["~TEXT"]);
?>