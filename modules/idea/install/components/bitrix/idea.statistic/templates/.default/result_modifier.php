<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
__IncludeLang(__DIR__."/lang/".LANGUAGE_ID."/result_modifier.php");

$TotalStatistic = array(
	"VALUE" => GetMessage("IDEA_STATISTIC_TOTAL_TITLE"),
	"CNT" => 0,
);

foreach($arResult as $key=>$StatusStatistic)
{
	$TotalStatistic["CNT"] += $StatusStatistic["CNT"];
	$arResult[$key]["URL"] = str_replace("#status_code#", mb_strtolower($StatusStatistic["XML_ID"]), $arParams["PATH_WITH_STATUS"]);
}

$arResult["ALL"] = $TotalStatistic;
$arResult["ALL"]["URL"] = $arParams["PATH_TO_INDEX"];

//No category
if(array_key_exists(0, $arResult))
{
	$arResult[0]["URL"] = $arParams["PATH_TO_INDEX"];
	$arResult[0]["VALUE"] = GetMessage("IDEA_STATISTIC_NO_STATUS_TITLE");
}
?>