<?php

class CCountry
{
	public static function GetList($by = 's_name', $order = 'asc', $arFilter = [])
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$arSqlSearch = Array();
		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				if(is_array($val))
				{
					if(count($val) <= 0)
						continue;
				}
				else
				{
					if( ((string)$val == '') || ($val === "NOT_REF") )
						continue;
				}
				$match_value_set = array_key_exists($key."_EXACT_MATCH", $arFilter);
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
						if ($val!="ALL")
						{
							$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
							$arSqlSearch[] = GetFilterQuery("C.ID",$val,$match);
						}
						break;
					case "SHORT_NAME":
					case "NAME":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("C.".$key,$val,$match);
						break;
					case "SESSIONS1":
						$arSqlSearch[] = "C.SESSIONS>='".intval($val)."'";
						break;
					case "SESSIONS2":
						$arSqlSearch[] = "C.SESSIONS<='".intval($val)."'";
						break;
					case "NEW_GUESTS1":
						$arSqlSearch[] = "C.NEW_GUESTS>='".intval($val)."'";
						break;
					case "NEW_GUESTS2":
						$arSqlSearch[] = "C.NEW_GUESTS<='".intval($val)."'";
						break;
					case "HITS1":
						$arSqlSearch[] = "C.HITS>='".intval($val)."'";
						break;
					case "HITS2":
						$arSqlSearch[] = "C.HITS<='".intval($val)."'";
						break;
					case "EVENTS1":
						$arSqlSearch[] = "C.C_EVENTS>='".intval($val)."'";
						break;
					case "EVENTS2":
						$arSqlSearch[] = "C.C_EVENTS<='".intval($val)."'";
						break;
				}
			}
		}

		if ($by == "s_id")				$strSqlOrder = "ORDER BY C.ID";
		elseif ($by == "s_short_name")	$strSqlOrder = "ORDER BY C.SHORT_NAME";
		elseif ($by == "s_name")		$strSqlOrder = "ORDER BY C.NAME";
		elseif ($by == "s_sessions")	$strSqlOrder = "ORDER BY C.SESSIONS";
		elseif ($by == "s_dropdown")	$strSqlOrder = "ORDER BY C.NEW_GUESTS desc, C.NAME";
		elseif ($by == "s_new_guests")	$strSqlOrder = "ORDER BY C.NEW_GUESTS";
		elseif ($by == "s_hits")		$strSqlOrder = "ORDER BY C.HITS ";
		elseif ($by == "s_events")		$strSqlOrder = "ORDER BY C.C_EVENTS ";
		else
		{
			$strSqlOrder = "ORDER BY C.NAME";
		}

		if ($order == "desc")
		{
			$strSqlOrder .= " desc ";
		}
		else
		{
			$strSqlOrder .= " asc ";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				C.*,
				C.ID as REFERENCE_ID,
				".$DB->Concat("'['", "C.ID", "'] '", $DB->IsNull("C.NAME","''"))." as REFERENCE
			FROM
				b_stat_country C
			WHERE
			$strSqlSearch
			$strSqlOrder
			";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		return $res;
	}

	// returns arrays needed to plot graph and diagram
	public static function GetGraphArray($arFilter, &$arLegend)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		global $arCountryColor;
		$DB = CDatabase::GetModuleConnection('statistic');
		$arSqlSearch = Array();
		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				if(is_array($val))
				{
					if(count($val) <= 0)
						continue;
				}
				else
				{
					if( ((string)$val == '') || ($val === "NOT_REF") )
						continue;
				}
				$match_value_set = array_key_exists($key."_EXACT_MATCH", $arFilter);
				$key = strtoupper($key);
				switch($key)
				{
					case "COUNTRY_ID":
						if ($val!="NOT_REF")
							$arSqlSearch[] = GetFilterQuery("D.COUNTRY_ID",$val,"N");
						break;
					case "DATE1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "D.DATE_STAT>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "D.DATE_STAT<=".$DB->CharToDateFunction($val." 23:59:59", "FULL");
						break;
				}
			}
		}
		$arrDays = array();
		$arLegend = array();
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				".$DB->DateToCharFunction("D.DATE_STAT","SHORT")." DATE_STAT,
				".$DB->DateFormatToDB("DD", "D.DATE_STAT")." DAY,
				".$DB->DateFormatToDB("MM", "D.DATE_STAT")." MONTH,
				".$DB->DateFormatToDB("YYYY", "D.DATE_STAT")." YEAR,
				D.COUNTRY_ID,
				D.SESSIONS,
				D.NEW_GUESTS,
				D.HITS,
				D.C_EVENTS,
				C.NAME,
				C.SESSIONS TOTAL_SESSIONS,
				C.NEW_GUESTS TOTAL_NEW_GUESTS,
				C.HITS TOTAL_HITS,
				C.C_EVENTS TOTAL_C_EVENTS
			FROM
				b_stat_country_day D
				INNER JOIN b_stat_country C ON (C.ID = D.COUNTRY_ID)
			WHERE
				".$strSqlSearch."
			ORDER BY
				D.DATE_STAT, D.COUNTRY_ID
		";

		$rsD = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($arD = $rsD->Fetch())
		{
			$arrDays[$arD["DATE_STAT"]]["D"] = $arD["DAY"];
			$arrDays[$arD["DATE_STAT"]]["M"] = $arD["MONTH"];
			$arrDays[$arD["DATE_STAT"]]["Y"] = $arD["YEAR"];
			$arrDays[$arD["DATE_STAT"]][$arD["COUNTRY_ID"]]["SESSIONS"]		= $arD["SESSIONS"];
			$arrDays[$arD["DATE_STAT"]][$arD["COUNTRY_ID"]]["NEW_GUESTS"]	= $arD["NEW_GUESTS"];
			$arrDays[$arD["DATE_STAT"]][$arD["COUNTRY_ID"]]["HITS"]			= $arD["HITS"];
			$arrDays[$arD["DATE_STAT"]][$arD["COUNTRY_ID"]]["C_EVENTS"]		= $arD["C_EVENTS"];

			$arLegend[$arD["COUNTRY_ID"]]["NAME"] = $arD["NAME"];
			$arLegend[$arD["COUNTRY_ID"]]["SESSIONS"] += $arD["SESSIONS"];
			$arLegend[$arD["COUNTRY_ID"]]["NEW_GUESTS"] += $arD["NEW_GUESTS"];
			$arLegend[$arD["COUNTRY_ID"]]["HITS"] += $arD["HITS"];
			$arLegend[$arD["COUNTRY_ID"]]["C_EVENTS"] += $arD["C_EVENTS"];

			$arLegend[$arD["COUNTRY_ID"]]["TOTAL_SESSIONS"] = $arD["TOTAL_SESSIONS"];
			$arLegend[$arD["COUNTRY_ID"]]["TOTAL_NEW_GUESTS"] = $arD["TOTAL_NEW_GUESTS"];
			$arLegend[$arD["COUNTRY_ID"]]["TOTAL_HITS"] = $arD["TOTAL_HITS"];
			$arLegend[$arD["COUNTRY_ID"]]["TOTAL_C_EVENTS"] = $arD["TOTAL_C_EVENTS"];
		}
		$color_getnext = "";
		$total = sizeof($arLegend);
		foreach ($arLegend as $key => $arr)
		{
			if ($arCountryColor[$key] <> '')
			{
				$color = $arCountryColor[$key];
			}
			else
			{
				$color = GetNextRGB($color_getnext, $total);
				$color_getnext = $color;
			}
			$arr["COLOR"] = $color;
			$arLegend[$key] = $arr;
		}

		reset($arrDays);
		reset($arLegend);
		return $arrDays;
	}
}
