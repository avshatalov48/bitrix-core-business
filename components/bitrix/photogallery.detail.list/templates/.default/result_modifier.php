<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams["SHOW_CONTROLS"] = ($arParams["SHOW_CONTROLS"] == "Y" ? "Y" : "N");

$arResult["SECTIONS_LIST"] = array();
if ($arParams["PERMISSION"] >= "U")
{
	CModule::IncludeModule("iblock");
	$arFilter = array(
		"ACTIVE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y");
	if ($arParams["BEHAVIOUR"] == "USER")
	{
		$arFilter["!ID"] = $arResult["GALLERY"]["ID"];
		$arFilter["RIGHT_MARGIN"] = $arResult["GALLERY"]["RIGHT_MARGIN"];
		$arFilter["LEFT_MARGIN"] = $arResult["GALLERY"]["LEFT_MARGIN"];
	}
	$rsIBlockSectionList = CIBlockSection::GetTreeList($arFilter);
	$iDiff = ($arParams["BEHAVIOUR"] == "USER" ? 2 : 1);
	while ($arSection = $rsIBlockSectionList->GetNext())
	{
		$len = ($arSection["DEPTH_LEVEL"] - $iDiff);
		$arSection["NAME"] = ($len > 0 ? str_repeat(" . ", $len) : "").$arSection["NAME"];
		$arResult["SECTIONS_LIST"][$arSection["ID"]] = $arSection["NAME"];
	}
}

$arParams1 = array(
	"MAX_VOTE" => intval($arParams["MAX_VOTE"])<=0? 5: intval($arParams["MAX_VOTE"]),
	"VOTE_NAMES" => is_array($arParams["VOTE_NAMES"])? $arParams["VOTE_NAMES"]: array(),
	"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"]);
$arResult["VOTE_NAMES"] = array();
foreach($arParams1["VOTE_NAMES"] as $k=>$v)
{
	if($v <> '')
		$arResult["VOTE_NAMES"][]=htmlspecialcharsbx($v);
	if(count($arResult["VOTE_NAMES"])>=$arParams1["MAX_VOTE"])
		break;
}
?>