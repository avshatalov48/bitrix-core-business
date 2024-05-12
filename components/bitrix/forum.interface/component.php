<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (CModule::IncludeModule("forum"))
{
	$arResult["data"] = isset($arParams["~DATA"]) ? $arParams["~DATA"] : null;
	$arResult["head"] = isset($arParams["~HEAD"]) ? $arParams["~HEAD"] : null;
	do {
		$arResult["id"] = "id_".rand();
	} while(ForumGetEntity($arResult["id"]) !== false);
	$arParams["RETURN_DATA"] = "";
	$result = $this->IncludeComponentTemplate();
	if (!empty($arParams["RETURN_DATA"]))
		return $arParams["RETURN_DATA"];
}
?>
