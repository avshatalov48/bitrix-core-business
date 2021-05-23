<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$period = 0; $time = 0;
// ********************************************************************************
$arParams["FONT_MIN"] = intval($arParams["FONT_MIN"]) > 0 ? $arParams["FONT_MIN"] : 10;
$arParams["FONT_MAX"] = intval($arParams["FONT_MAX"]) > 0 ? $arParams["FONT_MAX"] : 20;
$arParams["FONT_RANGE"] = $arParams["FONT_MAX"] - $arParams["FONT_MIN"];

$arParams["ANGULARITY"] = 0;
$arParams["WIDTH"] = trim($arParams["WIDTH"]);
$unit = array();
preg_match("/^[\d\.]+(\%|px|pt|in)$/i", $arParams["WIDTH"], $unit);
$arParams["WIDTH"] = (empty($unit) ? "" : " style='width:".$arParams["WIDTH"].";'");
$arResult["CNT_ALL"] = count($arResult["CATEGORY"]);
// ********************************************************************************
if (is_array($arResult["CATEGORY"]))
{
	foreach ($arResult["CATEGORY"] as $key => $res)
	{
		$cnt = $res["CNT"];
		if ($period > 0  && (($time - $res["TIME"]) <= $period))
		{
			$cnt += ($arResult["CNT_MAX"] - $cnt)*($period - ($time - $res["TIME"]))/$period;
		}

		$font_size = ($cnt / $arResult["CNT_ALL"]) *
			(($arParams["FONT_RANGE"] * $arParams["ANGULARITY"]) + 1) * ($arParams["FONT_RANGE"] * $arParams["ANGULARITY"]) / 2 +
			pow(($cnt-$arResult["CNT_MIN"])/max(1, $arResult["CNT_MAX"]-$arResult["CNT_MIN"]), 0.8) *
			($arParams["FONT_RANGE"] * (1 - $arParams["ANGULARITY"]));

		$font_size = min($arParams["FONT_MAX"], intval($font_size + $arParams["FONT_MIN"]));
		$arResult["CATEGORY"][$key]["FONT_SIZE"] = $font_size;
	}
}
?>