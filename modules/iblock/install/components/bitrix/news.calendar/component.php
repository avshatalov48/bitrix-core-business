<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */


if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

unset($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);

$arParams["TYPE"] = $arParams["TYPE"]=="NEWS";
$arParams["SHOW_YEAR"] = $arParams["SHOW_YEAR"]=="Y";
$arParams["SHOW_TIME"] = $arParams["SHOW_TIME"]=="Y";

$arParams["MONTH_VAR_NAME"] = trim($arParams["MONTH_VAR_NAME"]);
if($arParams["MONTH_VAR_NAME"] == '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["MONTH_VAR_NAME"]))
	$arParams["MONTH_VAR_NAME"] = "month";
$arParams["YEAR_VAR_NAME"] = trim($arParams["YEAR_VAR_NAME"]);
if($arParams["YEAR_VAR_NAME"] == '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["YEAR_VAR_NAME"]))
	$arParams["YEAR_VAR_NAME"] = "year";

$arParams["TITLE_LEN"] = intval($arParams["TITLE_LEN"]);
if($arParams["TITLE_LEN"]<0)
	$arParams["TITLE_LEN"]=0;
$arParams["NEWS_COUNT"] = intval($arParams["NEWS_COUNT"]);

$arParams["SET_TITLE"] = $arParams["SET_TITLE"]=="Y";
$arParams["SHOW_CURRENT_DATE"] = $arParams["SHOW_CURRENT_DATE"]=="Y";
$arParams["SHOW_MONTH_LIST"] = $arParams["SHOW_MONTH_LIST"]=="Y";

$arParams["WEEK_START"] = intval($arParams["WEEK_START"]);
if($arParams["WEEK_START"] < 0)
	$arParams["WEEK_START"] = 0;
elseif($arParams["WEEK_START"] > 6)
	$arParams["WEEK_START"] = 6;

if($arParams["TYPE"] || !in_array($arParams["DATE_FIELD"], array("DATE_ACTIVE_FROM", "DATE_ACTIVE_TO", "TIMESTAMP_X", "DATE_CREATE")))
	$arParams["DATE_FIELD"] = "DATE_ACTIVE_FROM";

$arParams["DETAIL_URL"]=trim($arParams["DETAIL_URL"]);

$today = time();

$currentMonth = intval($_REQUEST[$arParams["MONTH_VAR_NAME"]]);
if($currentMonth<1 || $currentMonth>12)
	$currentMonth = intval(date("n", $today));

$currentYear = intval($_REQUEST[$arParams["YEAR_VAR_NAME"]]);
if($currentYear<1)
	$currentYear = intval(date("Y", $today));

$todayYear = intval(date("Y", $today));
$todayMonth = intval(date("n", $today));
$todayDay = intval(date("j", $today));

if($arParams["TYPE"])
{	//Do not show future news
	if($currentYear > $todayYear)
		return;
	if(($currentYear == $todayYear) && ($currentMonth > $todayMonth))
		return;
}

if($this->StartResultCache(false, array($currentMonth, $currentYear, $todayYear, $todayMonth, $todayDay)))
{

	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}

	$arParams["MONTH_URL"]=trim($arParams["MONTH_URL"]);
	if($arParams["MONTH_URL"] == '')
		$arParams["MONTH_URL"] = $APPLICATION->GetCurPageParam($arParams["MONTH_VAR_NAME"]."=#MONTH#&".$arParams["YEAR_VAR_NAME"]."=#YEAR#", array($arParams["MONTH_VAR_NAME"], $arParams["YEAR_VAR_NAME"]));

	$arResult["TITLE"] = GetMessage("IBL_NEWS_CAL_M_".date("n", mktime(0, 0, 0, $currentMonth, 1, $currentYear)))." ".$currentYear;

	$arResult["currentMonth"] = $currentMonth;
	$arResult["currentYear"] = $currentYear;

	$arResult["WEEK_DAYS"] = Array(
			array("FULL"=>GetMessage("IBL_NEWS_CAL_D_0"),"SHORT"=>GetMessage("IBL_NEWS_CAL_S_0")),
			array("FULL"=>GetMessage("IBL_NEWS_CAL_D_1"),"SHORT"=>GetMessage("IBL_NEWS_CAL_S_1")),
			array("FULL"=>GetMessage("IBL_NEWS_CAL_D_2"),"SHORT"=>GetMessage("IBL_NEWS_CAL_S_2")),
			array("FULL"=>GetMessage("IBL_NEWS_CAL_D_3"),"SHORT"=>GetMessage("IBL_NEWS_CAL_S_3")),
			array("FULL"=>GetMessage("IBL_NEWS_CAL_D_4"),"SHORT"=>GetMessage("IBL_NEWS_CAL_S_4")),
			array("FULL"=>GetMessage("IBL_NEWS_CAL_D_5"),"SHORT"=>GetMessage("IBL_NEWS_CAL_S_5")),
			array("FULL"=>GetMessage("IBL_NEWS_CAL_D_6"),"SHORT"=>GetMessage("IBL_NEWS_CAL_S_6")),
		);

	$i = $arParams["WEEK_START"];
	while($i > 0)
	{
		$arResult["WEEK_DAYS"][] = array_shift($arResult["WEEK_DAYS"]);
		$i--;
	}

	$arFilter = Array(
			"ACTIVE" => "Y",
			">=".$arParams["DATE_FIELD"] => date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT")), mktime(0,0,0,$currentMonth,1,$currentYear)),
			"<".$arParams["DATE_FIELD"] => date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT")), mktime(0,0,0,$currentMonth+1,1,$currentYear)),
			"IBLOCK_ID" => $arParams["IBLOCK_ID"]
		);

	$arSelectedFields = Array("ACTIVE", $arParams["DATE_FIELD"], "ID", "IBLOCK_ID", "SITE_ID", "DETAIL_PAGE_URL", "NAME", "LANG_DIR", "SORT", "IBLOCK_TYPE", "PREVIEW_TEXT", "PREVIEW_TEXT_TYPE");

	$dbItems = CIBlockElement::GetList(array($arParams["DATE_FIELD"]=>"ASC", "ID"=>"ASC"), $arFilter, false, false, $arSelectedFields);
	$dbItems->SetUrlTemplates($arParams["DETAIL_URL"]);

	while($arItem = $dbItems->GetNext())
	{
		$arDays[ConvertDateTime($arItem[$arParams["DATE_FIELD"]], CLang::GetDateFormat("SHORT"))][] = $arItem;
	}

	$bPrevM = false;
	$bPrevY = false;
	if($arParams["SHOW_YEAR"])
	{
		$arFilter = Array(
			"ACTIVE" => "Y",
			"<".$arParams["DATE_FIELD"] => date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT")), mktime(0,0,0,1,1,$currentYear)),
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		);
		$dbItems = CIBlockElement::GetList(array(), $arFilter, false, array("nTopCount"=>1), Array("ID", "IBLOCK_ID"));
		if($arItem = $dbItems->GetNext())
		{
			$bPrevM = true;
			$bPrevY = true;
		}
	}

	if(!$bPrevM)
	{
		$arFilter = Array(
			"ACTIVE" => "Y",
			"<".$arParams["DATE_FIELD"] => date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT")), mktime(0,0,0,$currentMonth,1,$currentYear)),
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		);
		$dbItems = CIBlockElement::GetList(array(), $arFilter, false, array("nTopCount"=>1), Array("ID", "IBLOCK_ID"));
		if($arItem = $dbItems->GetNext())
			$bPrevM = true;
	}

	//For news we will not check future month for more news
	//for events we will.
	$bCheckNext = true;
	if($arParams["TYPE"])
	{
		$bCheckNext = mktime(0, 0, 0 ,$currentMonth+1, 1, $currentYear) < mktime(0, 0, 0 ,$todayMonth+1, 1, $todayYear);
	}

	$bNextM = false;
	$bNextY = false;
	if($bCheckNext)
	{
		if($arParams["SHOW_YEAR"])
		{
			$arFilter = Array(
				"ACTIVE" => "Y",
				">=".$arParams["DATE_FIELD"] => date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT")), mktime(0,0,0,1,1,$currentYear+1)),
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			);
			$dbItems = CIBlockElement::GetList(array(), $arFilter, false, array("nTopCount"=>1), Array("ID", "IBLOCK_ID"));
			if($arItem = $dbItems->GetNext())
			{
				$bNextM = true;
				$bNextY = true;
			}
		}

		if(!$bNextM)
		{
			$arFilter = Array(
				"ACTIVE" => "Y",
				">=".$arParams["DATE_FIELD"] => date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT")), mktime(0,0,0,$currentMonth+1,1,$currentYear)),
				"IBLOCK_ID" => $arParams["IBLOCK_ID"]
			);
			$dbItems = CIBlockElement::GetList(array(), $arFilter, false, array("nTopCount"=>1), Array("ID", "IBLOCK_ID"));
			if($arItem = $dbItems->GetNext())
				$bNextM = true;
		}
	}

	if($bPrevM)
	{
		$navM = date("m", mktime(0,0,0,$currentMonth-1, 1, $currentYear));
		$navY = date("Y", mktime(0,0,0,$currentMonth-1, 1, $currentYear));
		$arResult["PREV_MONTH"] = date("n", mktime(0,0,0,$currentMonth-1, 1, $currentYear));
		$arResult["PREV_MONTH_URL"] = htmlspecialcharsbx(str_replace(
			array("#YEAR#","#MONTH#"),
			array($navY, $navM),
			$arParams["MONTH_URL"]
		));
		$arResult["PREV_MONTH_URL_TITLE"] = GetMessage("IBL_NEWS_CAL_M_".$arResult["PREV_MONTH"]);
	}
	else
	{
		$arResult["PREV_MONTH_URL"] = false;
	}

	if($bNextM)
	{
		$navM = date("m", mktime(0,0,0,$currentMonth+1, 1, $currentYear));
		$navY = date("Y", mktime(0,0,0,$currentMonth+1, 1, $currentYear));
		$arResult["NEXT_MONTH"] = date("n", mktime(0,0,0,$currentMonth+1, 1, $currentYear));
		$arResult["NEXT_MONTH_URL"] = htmlspecialcharsbx(str_replace(
			array("#YEAR#","#MONTH#"),
			array($navY, $navM),
			$arParams["MONTH_URL"]
		));
		$arResult["NEXT_MONTH_URL_TITLE"] = GetMessage("IBL_NEWS_CAL_M_".$arResult["NEXT_MONTH"]);
	}
	else
	{
		$arResult["NEXT_MONTH_URL"] = false;
	}

	if($bPrevY)
	{
		$navM = date("m", mktime(0,0,0,$currentMonth, 1, $currentYear));
		$navY = date("Y", mktime(0,0,0,$currentMonth, 1, $currentYear-1));
		$arResult["PREV_YEAR"] = $navY;
		$arResult["PREV_YEAR_URL"] = htmlspecialcharsbx(str_replace(
			array("#YEAR#","#MONTH#"),
			array($navY, $navM),
			$arParams["MONTH_URL"]
		));
		$arResult["PREV_YEAR_URL_TITLE"] = $arResult["PREV_YEAR"];
	}
	else
	{
		$arResult["PREV_YEAR_URL"] = false;
	}

	if($bNextY)
	{
		$navM = date("m", mktime(0,0,0,$currentMonth, 1, $currentYear));
		$navY = date("Y", mktime(0,0,0,$currentMonth, 1, $currentYear+1));
		$arResult["NEXT_YEAR"] = $navY;
		$arResult["NEXT_YEAR_URL"] = htmlspecialcharsbx(str_replace(
			array("#YEAR#","#MONTH#"),
			array($navY, $navM),
			$arParams["MONTH_URL"]
		));
		$arResult["NEXT_YEAR_URL_TITLE"] = $arResult["NEXT_YEAR"];
	}
	else
	{
		$arResult["NEXT_YEAR_URL"] = false;
	}

	$date = mktime(0, 0, 0, $currentMonth, 1, $currentYear);
	$MonthStart =  date("w", $date) - $arParams["WEEK_START"];

	if($MonthStart < 0)
		$MonthStart += 7;

	$arResult["MONTH"] = array();
	$bBreak = false;
	for ($i = 0; $i < 6; $i++)
	{
		$arWeek = array();
		$row = $i * 7;
		for ($j = 0; $j < 7; $j++)
		{
			$arDay = array();

			$date = mktime(0, 0, 0, $currentMonth, (1 + $row + $j) - $MonthStart, $currentYear);
			$y = intval(date("Y", $date));
			$m = intval(date("n", $date));
			$d = intval(date("j", $date));
			$itm = date("w", $date);

			if ($i > 0 && $j == 0 && $currentMonth != $m)
			{
				$bBreak = true;
				break;
			}

			$dayClassName = "NewsCalDay";
			if ($d == $todayDay && $m == $todayMonth && $y == $todayYear && !$bBreak)
				$defaultClassName = "NewsCalToday";
			elseif ($currentMonth != $m)
			{
				$defaultClassName = "NewsCalOtherMonth";
				$dayClassName = "NewsCalDayOther";
			}
			elseif ($itm == 0 || $itm == 6)
				$defaultClassName = "NewsCalWeekend";
			else
				$defaultClassName = "NewsCalDefault";

			$arDay["day"] = $d;
			$arDay["td_class"] = $defaultClassName;
			$arDay["tday_class"] = $dayClassName;
			$arDay["events"] = array();

			$tmpDate = date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT")), mktime(0,0,0,$m,$d,$y));
			if(is_set($arDays[$tmpDate]))
			{
				$nn = 0;
				foreach($arDays[$tmpDate] as $dayNews)
				{
					$nn++;
					$eTime = "";
					$arTime = Array();
					if($arParams["SHOW_TIME"])
					{
						$arTime = ParseDateTime($dayNews["DATE_ACTIVE_FROM"], CLang::GetDateFormat("FULL"));
						if(intval($arTime["HH"])>0 || $arTime["MI"]>0)
							$eTime = $arTime["HH"].":".$arTime["MI"]."&nbsp;";
					}
					if($dayNews["PREVIEW_TEXT_TYPE"] == "text" && $dayNews["PREVIEW_TEXT"] <> '')
						$sTitle = TruncateText($dayNews["PREVIEW_TEXT"], 100);
					else
						$sTitle = $dayNews["NAME"];
					if($arParams["TITLE_LEN"]>0)
						$title = TruncateText($dayNews["NAME"], $arParams["TITLE_LEN"]);
					else
						$title = $dayNews["NAME"];
					$arDay["events"][] = array(
						"time"=>$eTime,
						"url"=>$dayNews["DETAIL_PAGE_URL"],
						"title"=>$title,
						"preview"=>$sTitle,
						$arParams["DATE_FIELD"]=>$dayNews[$arParams["DATE_FIELD"]],
					);
					if($arParams["NEWS_COUNT"]>0 && $arParams["NEWS_COUNT"]<=$nn)
						break;
				}
			}
			$arWeek[]=$arDay;
		}
		if ($bBreak)
			break;
		$arResult["MONTH"][]=$arWeek;
	}

	if($arParams["SHOW_MONTH_LIST"])
	{
		$arResult["SHOW_MONTH_LIST"] = array();
		for($i=1;$i<=12;$i++)
		{
			$url = str_replace(
				array("#YEAR#","#MONTH#"),
				array($arResult["currentYear"], $i),
				$arParams["MONTH_URL"]
			);
			if(defined("BX_AJAX_PARAM_ID"))
			{
				$p = mb_strpos($url, "?");
				if($p !== false)
				{
					$url .= "&".BX_AJAX_PARAM_ID."=".$arParams['AJAX_ID'];
				}
				else
				{
					$url .= "?".BX_AJAX_PARAM_ID."=".$arParams['AJAX_ID'];
				}
			}
			$arResult["SHOW_MONTH_LIST"][$i] = array(
				"VALUE" => htmlspecialcharsbx($url),
				"DISPLAY" => GetMessage("IBL_NEWS_CAL_M_".$i),
			);
		}
	}
	else
	{
		$arResult["SHOW_MONTH_LIST"] = false;
	}

	$this->SetResultCacheKeys(array(
		"TITLE",
	));
	$this->IncludeComponentTemplate();
}

if($arParams["SET_TITLE"])
	$APPLICATION->SetTitle($arResult["TITLE"], array('COMPONENT_NAME' => $this->GetName()));

?>
