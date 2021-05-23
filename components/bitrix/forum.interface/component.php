<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (CModule::IncludeModule("forum"))
{
	$arResult["data"] = $arParams["~DATA"];
	$arResult["head"] = $arParams["~HEAD"];
	do {
		$arResult["id"] = "id_".rand();
	} while(ForumGetEntity($arResult["id"]) !== false);
	$arParams["RETURN_DATA"] = "";
	$result = $this->IncludeComponentTemplate();
	if (!empty($arParams["RETURN_DATA"]))
		return $arParams["RETURN_DATA"];
}
?>