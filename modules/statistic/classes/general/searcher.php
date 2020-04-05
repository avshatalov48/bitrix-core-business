<?php
class CAllSearcher
{
	public static function DynamicDays($SEARCHER_ID, $date1="", $date2="")
	{
		$by = "";
		$order = "";
		$arMaxMin = array();
		$arFilter = array("DATE1"=>$date1, "DATE2"=>$date2);
		$z = CSearcher::GetDynamicList($SEARCHER_ID, $by, $order, $arMaxMin, $arFilter);
		$d = 0;
		while($zr = $z->Fetch())
			if(intval($zr["TOTAL_HITS"]) > 0)
				$d++;
		return $d;
	}

	// returns arrays needed to plot site indexing graph
	public static function GetGraphArray($arFilter, &$arrLegend)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$arSqlSearch = Array("D.SEARCHER_ID <> 1");

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
					if( (strlen($val) <= 0) || ($val === "NOT_REF") )
						continue;
				}

				$key = strtoupper($key);
				switch($key)
				{
					case "SEARCHER_ID":
						$arSqlSearch[] = GetFilterQuery("D.SEARCHER_ID",$val,"N");
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
		$arrLegend = array();
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$summa = $arFilter["SUMMA"]=="Y" ? "Y" : "N";
		$strSql = CSearcher::GetGraphArray_SQL($strSqlSearch);

		$rsD = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($arD = $rsD->Fetch())
		{
			$arrDays[$arD["DATE_STAT"]]["D"] = $arD["DAY"];
			$arrDays[$arD["DATE_STAT"]]["M"] = $arD["MONTH"];
			$arrDays[$arD["DATE_STAT"]]["Y"] = $arD["YEAR"];
			if ($summa=="N")
			{
				$arrDays[$arD["DATE_STAT"]][$arD["SEARCHER_ID"]]["TOTAL_HITS"] = $arD["TOTAL_HITS"];
				$arrLegend[$arD["SEARCHER_ID"]]["COUNTER_TYPE"] = "DETAIL";
				$arrLegend[$arD["SEARCHER_ID"]]["NAME"] = $arD["NAME"];
			}
			elseif ($summa=="Y")
			{
				$arrDays[$arD["DATE_STAT"]]["TOTAL_HITS"] += $arD["TOTAL_HITS"];
				$arrLegend[0]["COUNTER_TYPE"] = "TOTAL";
			}
		}

		$color = "";
		$total = sizeof($arrLegend);
		foreach ($arrLegend as $key => $arr)
		{
			$color = GetNextRGB($color, $total);
			$arrLegend[$key]["COLOR"] = $color;
		}

		return $arrDays;
	}

	public static function GetDomainList(&$by, &$order, $arFilter=Array(), &$is_filtered)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$arSqlSearch = Array("P.SEARCHER_ID <> 1");

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
					if( (strlen($val) <= 0) || ($val === "NOT_REF") )
						continue;
				}
				$match_value_set = array_key_exists($key."_EXACT_MATCH", $arFilter);
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
					case "SEARCHER_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("P.".$key,$val,$match);
						break;
					case "DOMAIN":
					case "VARIABLE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("P.".$key, $val, $match);
						break;
				}
			}
		}

		if ($by == "s_id") $strSqlOrder = "ORDER BY P.ID";
		elseif ($by == "s_domain") $strSqlOrder = "ORDER BY P.DOMAIN";
		elseif ($by == "s_variable") $strSqlOrder = "ORDER BY P.VARIABLE";
		else
		{
			$by = "s_id";
			$strSqlOrder = "ORDER BY P.ID";
		}
		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				P.ID,
				P.DOMAIN,
				P.VARIABLE,
				P.CHAR_SET
			FROM
				b_stat_searcher_params P
			WHERE
			$strSqlSearch
			$strSqlOrder
			";

		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch));
		return $rs;
	}

	public static function GetByID($ID)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$ID = intval($ID);
		$strSql = "SELECT S.* FROM b_stat_searcher S WHERE S.ID = '$ID'";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}
}
