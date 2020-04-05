<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$period = 0; $time = 0;
// ********************************************************************************
$arParams["FONT_MIN"] = intVal($arParams["FONT_MIN"]) > 0 ? $arParams["FONT_MIN"] : 10;
$arParams["FONT_MAX"] = intVal($arParams["FONT_MAX"]) > 0 ? $arParams["FONT_MAX"] : 50;
$arParams["FONT_RANGE"] = $arParams["FONT_MAX"] - $arParams["FONT_MIN"];
//$arParams["ANGULARITY"] = floatval($arParams["ANGULARITY"]) > 0 ? $arParams["ANGULARITY"] : 0.7;
$arParams["ANGULARITY"] = 0;
if (strLen($arParams["COLOR_OLD"]) == 6 && hexdec($arParams["COLOR_OLD"]) > 0)
	$arParams["COLOR_OLD"] = array(hexdec(substr($arParams["COLOR_OLD"], 0, 2)), hexdec(substr($arParams["COLOR_OLD"], 2, 2)), hexdec(substr($arParams["COLOR_OLD"], 4, 2)));
else
	$arParams["COLOR_OLD"] = array(200, 200, 200);
if (strLen($arParams["COLOR_NEW"]) == 6 && hexdec($arParams["COLOR_NEW"]) > 0)
{
	$arParams["COLOR_NEW"] = array(hexdec(substr($arParams["COLOR_NEW"], 0, 2)), hexdec(substr($arParams["COLOR_NEW"], 2, 2)), hexdec(substr($arParams["COLOR_NEW"], 4, 2)));
}
else
{
	$arParams["COLOR_NEW"] = array(0, 0, 0);
}
$arParams["WIDTH"] = trim($arParams["WIDTH"]);
$unit = array();
preg_match("/^[\d\.]+(\%|px|pt|in)$/i", $arParams["WIDTH"], $unit);
$arParams["WIDTH"] = (empty($unit) ? "" : " style='width:".$arParams["WIDTH"].";'");
$arParams["COLOR_TYPE"] = ($arParams["COLOR_TYPE"] != "N" ? "LOGORIFM" : "REAL");


if ($arParams["COLOR_TYPE"] == "LOGORIFM")
{
	asort($arResult["DATE"]);
	$aColors = array_keys($arResult["DATE"]);
	$aColors = array_flip($aColors);
	$iColorCount = count($aColors);
}

if (intVal($arParams["PERIOD_NEW_TAGS"]) > 0)
{
	$time = time()+CTimeZone::GetOffset();
	$period = intVal($arParams["PERIOD_NEW_TAGS"])*24*3600;
}
// ********************************************************************************
if (is_array($arResult["SEARCH"]))
{
	foreach ($arResult["SEARCH"] as $key => $res)
	{
		if ($arResult["CNT_ALL"] != 0)
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

			$font_size = min($arParams["FONT_MAX"], intVal($font_size + $arParams["FONT_MIN"]));
		}
		$color = "";
		foreach($arParams["COLOR_OLD"] as $k => $old)
		{
			$new = $arParams["COLOR_NEW"][$k];

			if ($arParams["COLOR_TYPE"] == "LOGORIFM" && $iColorCount > 0)
				$new_val = $aColors[$res["NAME"]]/$iColorCount;
			elseif ($arParams["COLOR_TYPE"] == "REAL" && (intVal($arResult["TIME_MAX"] - $arResult["TIME_MIN"]) > 0))
				$new_val = ($res["TIME"] - $arResult["TIME_MIN"])/($arResult["TIME_MAX"] - $arResult["TIME_MIN"]);
			else
				$new_val = 0;

			$color .= str_pad(dechex(intVal($old + ($new-$old)*$new_val)), 2, "0", STR_PAD_LEFT);
		}
		$color = strtoupper(str_replace("_", "", $color));
		$arResult["SEARCH"][$key]["FONT_SIZE"] = $font_size;
		$arResult["SEARCH"][$key]["COLOR"] = $color;
	}
}
?>