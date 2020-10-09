<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

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