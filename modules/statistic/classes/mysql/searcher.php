<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/classes/general/searcher.php");

class CSearcher extends CAllSearcher
{
	public static function GetGraphArray_SQL($strSqlSearch)
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$strSql = "
			SELECT
				".$DB->DateToCharFunction("D.DATE_STAT","SHORT")." DATE_STAT,
				DAYOFMONTH(D.DATE_STAT) DAY,
				MONTH(D.DATE_STAT) MONTH,
				YEAR(D.DATE_STAT) YEAR,
				D.SEARCHER_ID,
				D.TOTAL_HITS,
				C.NAME
			FROM
				b_stat_searcher_day D
			INNER JOIN b_stat_searcher C ON (C.ID = D.SEARCHER_ID)
			WHERE
				$strSqlSearch
			ORDER BY
				D.DATE_STAT, D.SEARCHER_ID
			";
		return $strSql;
	}

	public static function GetList($by = 's_today_hits', $order = 'desc', $arFilter = [], &$is_filtered = false, $LIMIT = false)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$arSqlSearch = Array("S.ID <> 1");
		$arSqlSearch_h = Array();
		$strSqlSearch_h = "";
		$filter_period = false;
		$strSqlPeriod = "";
		$strT = "";
		if (is_array($arFilter))
		{
			ResetFilterLogic();
			$date1 = $arFilter["DATE1_PERIOD"];
			$date2 = $arFilter["DATE2_PERIOD"];
			$date_from = MkDateTime(ConvertDateTime($date1,"D.M.Y"),"d.m.Y");
			$date_to = MkDateTime(ConvertDateTime($date2,"D.M.Y")." 23:59","d.m.Y H:i");
			if (CheckDateTime($date1) && $date1 <> '')
			{
				$filter_period = true;
				if ($date2 <> '')
				{
					$strSqlPeriod = "sum(if(D.DATE_STAT<FROM_UNIXTIME('$date_from'),0, if(D.DATE_STAT>FROM_UNIXTIME('$date_to'),0,";
					$strT=")))";
				}
				else
				{
					$strSqlPeriod = "sum(if(D.DATE_STAT<FROM_UNIXTIME('$date_from'),0,";
					$strT="))";
				}
			}
			elseif (CheckDateTime($date2) && $date2 <> '')
			{
				ResetFilterLogic();
				$filter_period = true;
				$strSqlPeriod = "sum(if(D.DATE_STAT>FROM_UNIXTIME('$date_to'),0,";
				$strT="))";
			}

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
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("S.ID",$val,$match);
						break;
					case "ACTIVE":
					case "SAVE_STATISTIC":
					case "DIAGRAM_DEFAULT":
						$arSqlSearch[] = ($val=="Y") ? "S.".$key."='Y'" : "S.".$key."='N'";
						break;
					case "HITS1":
						$arSqlSearch_h[] = "(sum(ifnull(D.TOTAL_HITS,0))+ifnull(S.TOTAL_HITS,0))>='".intval($val)."'";
						break;
					case "HITS2":
						$arSqlSearch_h[] = "(sum(ifnull(D.TOTAL_HITS,0))+ifnull(S.TOTAL_HITS,0))<='".intval($val)."'";
						break;
					case "DATE1":
						if (CheckDateTime($val))
							$arSqlSearch_h[] = "max(D.DATE_LAST)>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE2":
						if (CheckDateTime($val))
							$arSqlSearch_h[] = "max(D.DATE_LAST)<".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
						break;
					case "NAME":
					case "USER_AGENT":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("S.".$key, $val, $match);
						break;
				}
			}
		}

		if ($by == "s_id")
			$strSqlOrder = "ORDER BY S.ID";
		elseif ($by == "s_date_last")
			$strSqlOrder = "ORDER BY S_DATE_LAST";
		elseif ($by == "s_today_hits")
			$strSqlOrder = "ORDER BY TODAY_HITS";
		elseif ($by == "s_yesterday_hits")
			$strSqlOrder = "ORDER BY YESTERDAY_HITS";
		elseif ($by == "s_b_yesterday_hits")
			$strSqlOrder = "ORDER BY B_YESTERDAY_HITS";
		elseif ($by == "s_total_hits")
			$strSqlOrder = "ORDER BY TOTAL_HITS";
		elseif ($by == "s_period_hits")
			$strSqlOrder = "ORDER BY PERIOD_HITS";
		elseif ($by == "s_name")
			$strSqlOrder = "ORDER BY S.NAME";
		elseif ($by == "s_user_agent")
			$strSqlOrder = "ORDER BY S.USER_AGENT";
		elseif ($by == "s_chart")
			$strSqlOrder = "ORDER BY S.DIAGRAM_DEFAULT desc, TOTAL_HITS ";
		elseif ($by == "s_stat")
			$strSqlOrder = "ORDER BY TODAY_HITS desc, YESTERDAY_HITS desc, B_YESTERDAY_HITS desc, TOTAL_HITS desc, PERIOD_HITS";
		else
		{
			$strSqlOrder = "ORDER BY TODAY_HITS desc, YESTERDAY_HITS desc, B_YESTERDAY_HITS desc, TOTAL_HITS desc, PERIOD_HITS";
		}

		if ($order != "asc")
		{
			$strSqlOrder .= " desc ";
		}

		$limit_sql = "LIMIT ".intval(COption::GetOptionString('statistic','RECORDS_LIMIT'));
		if (intval($LIMIT)>0)
			$limit_sql = "LIMIT ".intval($LIMIT);

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		foreach($arSqlSearch_h as $sqlWhere)
			$strSqlSearch_h .= " and (".$sqlWhere.") ";

		$strSql =	"
		SELECT
			S.ID,
			S.TOTAL_HITS,
			S.USER_AGENT,
			S.DIAGRAM_DEFAULT,
			".$DB->DateToCharFunction("max(D.DATE_LAST)")."						DATE_LAST,
			max(ifnull(D.DATE_LAST,'1980-01-01'))								S_DATE_LAST,
			sum(ifnull(D.TOTAL_HITS,0))+ifnull(S.TOTAL_HITS,0)					TOTAL_HITS,
			sum(if(to_days(curdate())=to_days(D.DATE_STAT),ifnull(D.TOTAL_HITS,0),0))	TODAY_HITS,
			sum(if(to_days(curdate())-to_days(D.DATE_STAT)=1,ifnull(D.TOTAL_HITS,0),0))	YESTERDAY_HITS,
			sum(if(to_days(curdate())-to_days(D.DATE_STAT)=2,ifnull(D.TOTAL_HITS,0),0))	B_YESTERDAY_HITS,
			".($filter_period ? $strSqlPeriod.'ifnull(D.TOTAL_HITS,0)'.$strT.' PERIOD_HITS, '	: '0 PERIOD_HITS,')."
			S.NAME
		FROM
			b_stat_searcher S
		LEFT JOIN b_stat_searcher_day D ON (D.SEARCHER_ID = S.ID)
		WHERE
		$strSqlSearch
		and S.ID<>1
		GROUP BY S.ID
		HAVING
			'1'='1'
			$strSqlSearch_h
		$strSqlOrder
		$limit_sql
		";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch) || $filter_period || $strSqlSearch_h <> '');
		return $res;
	}

	public static function GetDropDownList($strSqlOrder="ORDER BY NAME, ID")
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$strSql = "
			SELECT
				ID as REFERENCE_ID,
				concat(ifnull(NAME,''),' [',ID,']') as REFERENCE
			FROM
				b_stat_searcher
			WHERE
				ID <> 1
			$strSqlOrder
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetDynamicList($SEARCHER_ID, $by = 's_date', $order = 'desc', &$arMaxMin = [], $arFilter = [])
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$SEARCHER_ID = intval($SEARCHER_ID);
		$arSqlSearch = Array();
		$strSqlSearch = "";
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

				$key = strtoupper($key);
				switch($key)
				{
					case "DATE1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "D.DATE_STAT>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "D.DATE_STAT<".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
						break;
				}
			}
		}

		foreach($arSqlSearch as $sqlWhere)
			$strSqlSearch .= " and (".$sqlWhere.") ";

		if ($by == "s_date") $strSqlOrder = "ORDER BY D.DATE_STAT";
		else
		{
			$strSqlOrder = "ORDER BY D.DATE_STAT";
		}

		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
		}

		$strSql =	"
			SELECT
				".$DB->DateToCharFunction("D.DATE_STAT","SHORT")."		DATE_STAT,
				DAYOFMONTH(D.DATE_STAT)									DAY,
				MONTH(D.DATE_STAT)										MONTH,
				YEAR(D.DATE_STAT)										YEAR,
				D.TOTAL_HITS
			FROM
				b_stat_searcher_day D
			WHERE
				D.SEARCHER_ID = $SEARCHER_ID
			$strSqlSearch
			$strSqlOrder
			";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		$strSql = "
			SELECT
				max(D.DATE_STAT)				DATE_LAST,
				min(D.DATE_STAT)				DATE_FIRST,
				DAYOFMONTH(max(D.DATE_STAT))	MAX_DAY,
				MONTH(max(D.DATE_STAT))			MAX_MONTH,
				YEAR(max(D.DATE_STAT))			MAX_YEAR,
				DAYOFMONTH(min(D.DATE_STAT))	MIN_DAY,
				MONTH(min(D.DATE_STAT))			MIN_MONTH,
				YEAR(min(D.DATE_STAT))			MIN_YEAR
			FROM
				b_stat_searcher_day D
			WHERE
				D.SEARCHER_ID = $SEARCHER_ID
			$strSqlSearch
			";
		$a = $DB->Query($strSql, false, $err_mess.__LINE__);
		$ar = $a->Fetch();
		$arMaxMin["MAX_DAY"]	= $ar["MAX_DAY"];
		$arMaxMin["MAX_MONTH"]	= $ar["MAX_MONTH"];
		$arMaxMin["MAX_YEAR"]	= $ar["MAX_YEAR"];
		$arMaxMin["MIN_DAY"]	= $ar["MIN_DAY"];
		$arMaxMin["MIN_MONTH"]	= $ar["MIN_MONTH"];
		$arMaxMin["MIN_YEAR"]	= $ar["MIN_YEAR"];
		return $res;
	}
}
