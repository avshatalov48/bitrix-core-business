<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arParams["DETAIL_URL_FOR_JS"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"],
	array(
		"USER_ALIAS" => $arParams["USER_ALIAS"],
		"SECTION_ID" => $arParams["SECTION_ID"],
		"ELEMENT_ID" =>'#element_id#')
);

if($arParams["BACK_URL"] == '' && $_REQUEST["BACK_URL"] <> '')
{
	$arParams["~BACK_URL"] = $_REQUEST["BACK_URL"];
	$arParams["BACK_URL"] = htmlspecialcharsbx($_REQUEST["BACK_URL"]);
}

$arResult["ELEMENT_FOR_JS"] = array_values($arResult["ELEMENTS_LIST_JS"]);
?>