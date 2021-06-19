<?php

class CPage
{
	public static function GetDynamicList($URL, $by = 's_date', $order = 'desc', $arFilter = [])
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$arSqlSearch = Array();
		$from_adv = "";
		$where_adv = "";
		$counter = "SUM(D.COUNTER)";
		$enter_counter = "SUM(D.ENTER_COUNTER)";
		$exit_counter = "SUM(if(D.EXIT_COUNTER>0,D.EXIT_COUNTER,0))";
		if (is_array($arFilter))
		{
			if ($arFilter["ADV"] <> '')
			{
				$from_adv = " , b_stat_page_adv A ";
				$where_adv = "and A.PAGE_ID = D.ID";

				if ($arFilter["ADV_DATA_TYPE"]=="B")
				{
					$counter = "SUM(A.COUNTER_BACK)";
					$enter_counter = "SUM(A.ENTER_COUNTER_BACK)";
					$exit_counter = "SUM(if(A.EXIT_COUNTER_BACK>0,A.EXIT_COUNTER_BACK,0))";
				}
				elseif ($arFilter["ADV_DATA_TYPE"]=="P")
				{
					$counter = "SUM(A.COUNTER)";
					$enter_counter = "SUM(A.ENTER_COUNTER)";
					$exit_counter = "SUM(if(A.EXIT_COUNTER>0,A.EXIT_COUNTER,0))";
				}
				else
				{
					$counter = "SUM(A.COUNTER + A.COUNTER_BACK)";
					$enter_counter = "SUM(A.ENTER_COUNTER + A.ENTER_COUNTER_BACK)";
					$exit_counter = "SUM(if(A.EXIT_COUNTER>0,A.EXIT_COUNTER,0) + if(A.EXIT_COUNTER_BACK>0,A.EXIT_COUNTER_BACK,0))";
				}
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
					case "DATE1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "D.DATE_STAT>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "D.DATE_STAT<".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
						break;
					case "ADV":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("A.ADV_ID",$val,$match);
						break;
					case "IS_DIR":
						$arSqlSearch[] = ($val=="Y") ? "D.DIR = 'Y'" : "D.DIR = 'N'";
						break;
				}
			}
		}

		if ($by == "s_date")
			$strSqlOrder = "ORDER BY D.DATE_STAT";
		else
		{
			$strSqlOrder = "ORDER BY D.DATE_STAT";
		}

		if ($order != "asc")
		{
			$strSqlOrder .= " desc ";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				".$DB->DateToCharFunction("D.DATE_STAT","SHORT")." DATE_STAT,
				DAYOFMONTH(D.DATE_STAT) DAY,
				MONTH(D.DATE_STAT) MONTH,
				YEAR(D.DATE_STAT) YEAR,
				$counter COUNTER,
				$enter_counter ENTER_COUNTER,
				$exit_counter EXIT_COUNTER
			FROM
				b_stat_page D
				$from_adv
			WHERE
			$strSqlSearch
			and D.URL_HASH = '".crc32ex($URL)."'
			$where_adv
			GROUP BY D.DATE_STAT
			$strSqlOrder
		";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetList($COUNTER_TYPE, $by = 's_counter', $order = 'desc', $arFilter = [])
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		if ($COUNTER_TYPE!="ENTER_COUNTER" && $COUNTER_TYPE!="EXIT_COUNTER")
			$COUNTER_TYPE = "COUNTER";
		$counter = "V.".$COUNTER_TYPE;
		$where_counter = "and V.".$COUNTER_TYPE.">0";
		$arSqlSearch = Array();
		$arSqlSearch_h = Array();
		$strSqlSearch_h = "";
		$from_adv = "";
		$where_adv = "";
		if (is_array($arFilter))
		{
			if ($arFilter["ADV"] <> '')
			{
				$from_adv = " , b_stat_page_adv A ";
				$where_adv = "and A.PAGE_ID = V.ID";

				if ($arFilter["ADV_DATA_TYPE"]=="B")
				{
					$counter = "A.".$COUNTER_TYPE."_BACK";
					$where_counter = "and A.".$COUNTER_TYPE."_BACK>0";
				}
				elseif ($arFilter["ADV_DATA_TYPE"]=="S")
				{
					$counter = "if(A.".$COUNTER_TYPE.">0, A.".$COUNTER_TYPE.", 0) + if(A.".$COUNTER_TYPE."_BACK>0, A.".$COUNTER_TYPE."_BACK, 0)";
					$where_counter = "and (A.".$COUNTER_TYPE."_BACK>0 or A.".$COUNTER_TYPE.">0)";
				}
				elseif ($arFilter["ADV_DATA_TYPE"]=="P")
				{
					$counter = "A.".$COUNTER_TYPE;
					$where_counter = "and A.".$COUNTER_TYPE.">0";
				}
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
					case "DATE1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "V.DATE_STAT>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "V.DATE_STAT<".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
						break;
					case "SHOW":
					case "DIR":
						$arSqlSearch[] = ($val=="D") ? "V.DIR='Y'" : "V.DIR='N'";
						break;
					case "SECTION":
					case "URL":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("V.URL",$val,$match,array("/","\\","_",".",":"));
						break;
					case "SECTION_ID":
					case "URL_ID":
						$arSqlSearch[] = "(V.URL like '".$DB->ForSql($val)."' and V.URL<>'".$DB->ForSql($val)."')";
						break;
					case "PAGE_404":
					case "URL_404":
						$arSqlSearch_h[] = ($val=="Y") ? "max(V.URL_404)='Y'" : "max(V.URL_404)='N'";
						break;
					case "ADV":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("A.ADV_ID",$val,$match);
						break;
					case "SITE_ID":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("V.SITE_ID", $val, $match);
						break;
				}
			}
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		foreach($arSqlSearch_h as $sqlWhere)
			$strSqlSearch_h .= " and (".$sqlWhere.") ";

		if ($by == "s_url")
			$strSqlOrder = "ORDER BY V.URL";
		elseif ($by == "s_counter")
			$strSqlOrder = "ORDER BY COUNTER";
		else
		{
			$strSqlOrder = "ORDER BY COUNTER desc, V.URL";
		}

		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
		}

		$strSql = "
			SELECT
				V.URL,
				V.DIR,
				V.SITE_ID,
				max(V.URL_404) as URL_404,
				sum($counter) as COUNTER
			FROM
				b_stat_page V
				$from_adv
			WHERE
			$strSqlSearch
			$where_adv
			$where_counter
			GROUP BY V.URL, V.DIR
			HAVING
				'1'='1'
				$strSqlSearch_h
			$strSqlOrder
			LIMIT ".intval(COption::GetOptionString('statistic','RECORDS_LIMIT'))."
			";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		return $res;
	}
}
