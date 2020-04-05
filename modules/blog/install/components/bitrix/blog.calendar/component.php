<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
$arParams["YEAR"] = (IntVal($arParams["YEAR"])>0 ? IntVal($arParams["YEAR"]) : false);
$arParams["MONTH"] = (IntVal($arParams["MONTH"])>0 ? IntVal($arParams["MONTH"]) : false);
$arParams["DAY"] = (IntVal($arParams["DAY"])>0 ? IntVal($arParams["DAY"]) : false);
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(IntVal($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
	
$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if(strlen($arParams["PATH_TO_BLOG"])<=0)
	$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");
if(strpos($arParams["PATH_TO_BLOG"], "?")===false)
{
	$arParams["PATH_TO_BLOG"] .= "?";
}
else
{
	$arParams["PATH_TO_BLOG"] .= "&amp;";
}

$today = time();
$todayYear = IntVal(date("Y", $today));
$todayMonth = IntVal(date("n", $today));
$todayDay = IntVal(date("j", $today));

$arParams["MONTH"] = IntVal($arParams["MONTH"]);
if ($arParams["MONTH"] < 1 || $arParams["MONTH"] > 12)
	$arParams["MONTH"] = $todayMonth;

$arParams["YEAR"] = IntVal($arParams["YEAR"]);
if ($arParams["YEAR"] < 1990 || $arParams["YEAR"] > 2020)
	$arParams["YEAR"] = $todayYear;

$arParams["DAY"] = IntVal($arParams["DAY"]);
$bSelectDay = (($arParams["DAY"] > 0) ? True : False);
if ($arParams["DAY"] < 1 || $arParams["DAY"] > 31)
	$arParams["DAY"] = $todayDay;

if ($arParams["YEAR"] > $todayYear || $arParams["YEAR"] == $todayYear && $arParams["MONTH"] > $todayMonth)
{
	$arParams["MONTH"] = $todayMonth;
	$arParams["YEAR"] = $todayYear;
}

$arResult["CALENDAR"] = Array();
if (StrLen($arParams["BLOG_URL"]) > 0)
{
	if($GLOBALS["USER"]->IsAuthorized())
		$arUserGroups = CBlogUser::GetUserGroups($USER->GetID(), $arBlog["ID"], "Y", BLOG_BY_USER_ID);
	else
		$arUserGroups = Array(1);

	$numUserGroups = count($arUserGroups);
	for ($i = 0; $i < $numUserGroups - 1; $i++)
	{
		for ($j = $i + 1; $j < $numUserGroups; $j++)
		{
			if ($arUserGroups[$i] > $arUserGroups[$j])
			{
				$tmpGroup = $arUserGroups[$i];
				$arUserGroups[$i] = $arUserGroups[$j];
				$arUserGroups[$j] = $tmpGroup;
			}
		}
	}

	$strUserGroups = "";
	for ($i = 0; $i < $numUserGroups; $i++)
		$strUserGroups .= "_".$arUserGroups[$i];

	$cache = new CPHPCache;
	$cache_id = "blog_calendar_".serialize($arParams).$strUserGroups;
	$cache_path = "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/calendar/";

	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$Vars = $cache->GetVars();
		foreach($Vars["arResult"] as $k=>$v)
			$arResult[$k] = $v;
		CBitrixComponentTemplate::ApplyCachedData($Vars["templateCachedData"]);	
		$cache->Output();
	}
	else
	{
		if ($arParams["CACHE_TIME"] > 0)
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			
		if ($arBlog = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]))
		{
			if($arBlog["ACTIVE"] == "Y")
			{
				$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
				if($arGroup["SITE_ID"] == SITE_ID)
				{
					$dbMinPost = CBlogPost::GetList(Array("DATE_PUBLISH" => "ASC"), Array("BLOG_ID" => $arBlog["ID"], "PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH), false, Array("nTopCount" => 1), Array("DATE_PUBLISH", "ID"));
					if($arMinPost = $dbMinPost->Fetch())
					{
						$minYear = date("Y", MakeTimeStamp($arMinPost["DATE_PUBLISH"]));
						$minMonth = date("n", MakeTimeStamp($arMinPost["DATE_PUBLISH"]));
					}
					else
					{
						$minYear = date("Y");
						$minMonth = date("n");
					}

					
					$arResult["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"]));

					$arDates = CBlogPost::GetListCalendar($arBlog["ID"], $arParams["YEAR"], $arParams["MONTH"], false);

					$arDays = array();
					for ($i = 0; $i < count($arDates); $i++)
						$arDays[IntVal($arDates[$i]["DAY"])] = true;

					$currentYear = $arParams["YEAR"];
					$currentMonth = $arParams["MONTH"];

					$lastMonthYear = $arParams["YEAR"];
					$lastMonth = $arParams["MONTH"] - 1;
					if ($lastMonth < 1)
					{
						$lastMonth = 12;
						$lastMonthYear = $lastMonthYear - 1;
					}

					$nextMonthYear = $arParams["YEAR"];
					$nextMonth = $arParams["MONTH"] + 1;
					if ($nextMonth > 12)
					{
						$nextMonth = 1;
						$nextMonthYear = $nextMonthYear + 1;
					}
					

					if (($lastMonthYear > $minYear) || ($lastMonthYear ==  $minYear && $lastMonth >= $minMonth))
					{
						$arResult["urlToPrevYear"] = $arResult["urlToBlog"]."year=".$lastMonthYear."&amp;month=".$lastMonth;
					}
					
					$arResult["PrevYear"] = $lastMonthYear;
					$arResult["PrevMonth"] = $lastMonth;
					$arResult["CurrentYear"] = $currentYear;
					$arResult["CurrentMonth"] = $currentMonth;
					$arResult["NextYear"] = $nextMonthYear;
					$arResult["NextMonth"] = $nextMonth;
					$arResult["TodayYear"] = $todayYear;
					$arResult["TodayMonth"] = $todayMonth;
					$arResult["TodayDay"] = $todayDay;
					
					if ($currentYear < $todayYear || $currentYear == $todayYear && $currentMonth < $todayMonth)
					{
						$arResult["urlToNextYear"] = $arResult["urlToBlog"]."year=".$nextMonthYear."&amp;month=".$nextMonth;
					}
					
					$firstDate = mktime(0, 0, 0, $currentMonth, 1, $currentYear);
					$firstDay = IntVal(date("w", $firstDate) - 1);
					if ($firstDay == -1)
						$firstDay = 6;

					$bBreak = false;
					$arResult["CALENDAR"] = Array();
					for ($i = 0; $i < 6; $i++)
					{
						$arResult["CALENDAR"][$i] = Array();
						$row = $i * 7;
						for ($j = 0; $j < 7; $j++)
						{
							$arResult["CALENDAR"][$i][$j] = Array();
							$date = mktime(0, 0, 0, $currentMonth, 1 - $firstDay + $row + $j, $currentYear);
							$y = intval(date("Y", $date));
							$m = intval(date("n", $date));
							$d = intval(date("j", $date));
							$arResult["CALENDAR"][$i][$j]["day"] = $d;
							
							if ($i > 0 && $d == 1)
								$bBreak = true;

							if ($bSelectDay && $d == $arParams["DAY"] && $m == $arParams["MONTH"] && $y == $arParams["YEAR"] && ($row + $j + 1) > $firstDay && !$bBreak)
								$arResult["CALENDAR"][$i][$j]["type"] = "selected";
							elseif ($d == $todayDay && $m == $todayMonth && $y == $todayYear && ($row + $j + 1) > $firstDay && !$bBreak)
								$arResult["CALENDAR"][$i][$j]["type"] = "today";
							elseif ($j == 5 || $j == 6)
								$arResult["CALENDAR"][$i][$j]["type"] = "weekend";
							if ($row + $j + 1 > $firstDay && !$bBreak)
							{
								if($arDays[$d] == true)
									$arResult["CALENDAR"][$i][$j]["link"] = $arResult["urlToBlog"]."year=".$y."&amp;month=".$m."&amp;day=".$d;
							}
							else
								$arResult["CALENDAR"][$i][$j]["day"] = "&nbsp;";
						}
						if ($bBreak)
							break;
					}
				}
			}
		}
		if ($arParams["CACHE_TIME"] > 0)
			$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
	}
}
$this->IncludeComponentTemplate();
?>