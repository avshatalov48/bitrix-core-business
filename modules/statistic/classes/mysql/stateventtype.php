<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/classes/general/stateventtype.php");

class CStatEventType extends CAllStatEventType
{
	public static function GetByID($ID)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$ID = intval($ID);
		$strSql =	"
			SELECT
				E.*,
				".$DB->DateToCharFunction("E.DATE_ENTER")."		DATE_ENTER,
				if (length(E.NAME)>0, E.NAME,
					concat(ifnull(E.EVENT1,''),' / ',ifnull(E.EVENT2,''))) EVENT
			FROM
				b_stat_event E
			WHERE
				E.ID = '$ID'
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetList(&$by, &$order, $arFilter=Array(), &$is_filtered, $LIMIT=false)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');

		$find_group = $arFilter["GROUP"];
		if($find_group!="event1" && $find_group!="event2" && $find_group!="total")
			$find_group="";

		$arSqlSearch = Array();
		$arSqlSearch_h = Array();
		$strSqlSearch_h = "";
		$filter_period = false;
		$strSqlPeriod = "";
		$strT = "";
		$CURRENCY = "";

		if (is_array($arFilter))
		{
			ResetFilterLogic();
			$date1 = $arFilter["DATE1_PERIOD"];
			$date2 = $arFilter["DATE2_PERIOD"];
			$date_from = MkDateTime(ConvertDateTime($date1,"D.M.Y"),"d.m.Y");
			$date_to = MkDateTime(ConvertDateTime($date2,"D.M.Y")." 23:59","d.m.Y H:i");
			if (strlen($date1)>0)
			{
				$filter_period = true;
				if (strlen($date2)>0)
				{
					$strSqlPeriod = "if(D.DATE_STAT<FROM_UNIXTIME('$date_from'),0, if(D.DATE_STAT>FROM_UNIXTIME('$date_to'),0,";
					$strT="))";
				}
				else
				{
					$strSqlPeriod = "if(D.DATE_STAT<FROM_UNIXTIME('$date_from'),0,";
					$strT=")";
				}
			}
			elseif (strlen($date2)>0)
			{
				$filter_period = true;
				$strSqlPeriod = "if(D.DATE_STAT>FROM_UNIXTIME('$date_to'),0,";
				$strT=")";
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
					if( (strlen($val) <= 0) || ($val === "NOT_REF") )
						continue;
				}
				$match_value_set = array_key_exists($key."_EXACT_MATCH", $arFilter);
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("E.".$key,$val,$match);
						break;
					case "DATE_ENTER_1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "E.DATE_ENTER>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_ENTER_2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "E.DATE_ENTER<".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
						break;
					case "DATE_LAST_1":
						if (CheckDateTime($val))
							$arSqlSearch_h[] = "max(D.DATE_LAST)>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_LAST_2":
						if (CheckDateTime($val))
							$arSqlSearch_h[] = "max(D.DATE_LAST)<".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
						break;
					case "EVENT1":
					case "EVENT2":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.".$key,$val,$match);
						break;
					case "COUNTER1":
						$arSqlSearch_h[] = "TOTAL_COUNTER>='".intval($val)."'";
						break;
					case "COUNTER2":
						$arSqlSearch_h[] = "TOTAL_COUNTER<='".intval($val)."'";
						break;
					case "MONEY1":
						$arSqlSearch_h[] = "TOTAL_MONEY>='".roundDB($val)."'";
						break;
					case "MONEY2":
						$arSqlSearch_h[] = "TOTAL_MONEY<='".roundDB($val)."'";
						break;
					case "ADV_VISIBLE":
					case "DIAGRAM_DEFAULT":
						$arSqlSearch[] = ($val=="Y") ? "E.".$key."='Y'" : "E.".$key."='N'";
						break;
					case "DESCRIPTION":
					case "NAME":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.".$key,$val,$match);
						break;
					case "KEEP_DAYS1":
						$arSqlSearch[] = "E.KEEP_DAYS>='".intval($val)."'";
						break;
					case "KEEP_DAYS2":
						$arSqlSearch[] = "E.KEEP_DAYS<='".intval($val)."'";
						break;
					case "DYNAMIC_KEEP_DAYS1":
						$arSqlSearch[] = "E.DYNAMIC_KEEP_DAYS>='".intval($val)."'";
						break;
					case "DYNAMIC_KEEP_DAYS2":
						$arSqlSearch[] = "E.DYNAMIC_KEEP_DAYS<='".intval($val)."'";
						break;
					case "CURRENCY":
						$CURRENCY = $val;
						break;
				}
			}
		}

		$rate = 1;
		$base_currency = GetStatisticBaseCurrency();
		$view_currency = $base_currency;
		if (strlen($base_currency)>0)
		{
			if (CModule::IncludeModule("currency"))
			{
				if ($CURRENCY!=$base_currency && strlen($CURRENCY)>0)
				{
					$rate = CCurrencyRates::GetConvertFactor($base_currency, $CURRENCY);
					$view_currency = $CURRENCY;
				}
			}
		}

		if ($by == "s_id" && $find_group=="")			$strSqlOrder = "ORDER BY E.ID";
		elseif ($by == "s_date_last")				$strSqlOrder = "ORDER BY E_DATE_LAST";
		elseif ($by == "s_date_enter")				$strSqlOrder = "ORDER BY DATE_ENTER";
		elseif ($by == "s_today_counter")			$strSqlOrder = "ORDER BY TODAY_COUNTER";
		elseif ($by == "s_yesterday_counter")			$strSqlOrder = "ORDER BY YESTERDAY_COUNTER";
		elseif ($by == "s_b_yesterday_counter")			$strSqlOrder = "ORDER BY B_YESTERDAY_COUNTER";
		elseif ($by == "s_total_counter")			$strSqlOrder = "ORDER BY TOTAL_COUNTER";
		elseif ($by == "s_period_counter")			$strSqlOrder = "ORDER BY PERIOD_COUNTER";
		elseif ($by == "s_name" && $find_group=="")		$strSqlOrder = "ORDER BY E.NAME";
		elseif ($by == "s_event1" && $find_group=="")		$strSqlOrder = "ORDER BY E.EVENT1";
		elseif ($by == "s_event1" && $find_group=="event1")	$strSqlOrder = "ORDER BY E.EVENT1";
		elseif ($by == "s_event2" && $find_group=="")		$strSqlOrder = "ORDER BY E.EVENT2";
		elseif ($by == "s_event2" && $find_group=="event2")	$strSqlOrder = "ORDER BY E.EVENT2";
		elseif ($by == "s_event12" && $find_group=="")		$strSqlOrder = "ORDER BY E.EVENT1, E.EVENT2";
		elseif ($by == "s_chart" && $find_group=="")		$strSqlOrder = "ORDER BY E.DIAGRAM_DEFAULT desc, TOTAL_COUNTER ";
		elseif ($by == "s_stat")				$strSqlOrder = "ORDER BY TODAY_COUNTER desc, YESTERDAY_COUNTER desc, B_YESTERDAY_COUNTER desc, TOTAL_COUNTER desc, PERIOD_COUNTER";
		else
		{
			$by = "s_today_counter";
			$strSqlOrder = "ORDER BY TODAY_COUNTER desc, YESTERDAY_COUNTER desc, B_YESTERDAY_COUNTER desc, TOTAL_COUNTER desc, PERIOD_COUNTER";
		}
		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		foreach($arSqlSearch_h as $sqlWhere)
			$strSqlSearch_h .= " and (".$sqlWhere.") ";

		$limit_sql = "LIMIT ".intval(COption::GetOptionString('statistic','RECORDS_LIMIT'));
		if (intval($LIMIT)>0) $limit_sql = "LIMIT ".intval($LIMIT);
		if ($find_group=="") // если группировка не выбрана
		{
			$strSql = "
			SELECT
				E.ID,
				E.EVENT1,
				E.EVENT2,
				E.COUNTER,
				E.DIAGRAM_DEFAULT,
				'".$DB->ForSql($view_currency)."'									CURRENCY,
				".$DB->DateToCharFunction("E.DATE_ENTER")."						DATE_ENTER,
				".$DB->DateToCharFunction("max(D.DATE_LAST)")."						DATE_LAST,
				max(ifnull(D.DATE_LAST,'1980-01-01'))							E_DATE_LAST,
				sum(ifnull(D.COUNTER,0))+ifnull(E.COUNTER,0)						TOTAL_COUNTER,
				sum(round(ifnull(D.MONEY,0)*$rate,2))+round(ifnull(E.MONEY,0)*$rate,2)			TOTAL_MONEY,
				sum(if(to_days(curdate())=to_days(D.DATE_STAT),ifnull(D.COUNTER,0),0))			TODAY_COUNTER,
				sum(if(to_days(curdate())-to_days(D.DATE_STAT)=1,ifnull(D.COUNTER,0),0))		YESTERDAY_COUNTER,
				sum(if(to_days(curdate())-to_days(D.DATE_STAT)=2,ifnull(D.COUNTER,0),0))		B_YESTERDAY_COUNTER,
				sum(".($filter_period ? $strSqlPeriod.'ifnull(D.COUNTER,0)'.$strT : 0).")		PERIOD_COUNTER,
				sum(round(if(to_days(curdate())-to_days(D.DATE_STAT)=0,ifnull(D.MONEY,0),0)*$rate,2))	TODAY_MONEY,
				sum(round(if(to_days(curdate())-to_days(D.DATE_STAT)=1,ifnull(D.MONEY,0),0)*$rate,2))	YESTERDAY_MONEY,
				sum(round(if(to_days(curdate())-to_days(D.DATE_STAT)=2,ifnull(D.MONEY,0),0)*$rate,2))	B_YESTERDAY_MONEY,
				sum(round(".($filter_period ? $strSqlPeriod.'ifnull(D.MONEY,0)'.$strT : 0)."*$rate,2))	PERIOD_MONEY,
				E.NAME,
				E.DESCRIPTION,
				if (length(E.NAME)>0, E.NAME, concat(E.EVENT1,' / ',ifnull(E.EVENT2,'')))		EVENT
			FROM
				b_stat_event E
			LEFT JOIN b_stat_event_day D ON (D.EVENT_ID = E.ID)
			WHERE
			$strSqlSearch
			GROUP BY E.ID
			HAVING
				'1'='1'
				$strSqlSearch_h
			$strSqlOrder
			$limit_sql
			";
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		}
		elseif ($find_group=="total")
		{
			$arResult = array(
				"TOTAL_COUNTER"		=>0,
				"TOTAL_MONEY"		=>0,
				"TODAY_COUNTER"		=>0,
				"YESTERDAY_COUNTER" 	=>0,
				"B_YESTERDAY_COUNTER"	=>0,
				"PERIOD_COUNTER"	=>0,
				"TODAY_MONEY"		=>0,
				"YESTERDAY_MONEY" 	=>0,
				"B_YESTERDAY_MONEY"	=>0,
				"PERIOD_MONEY"		=>0,
			);
			$strSql = "
			SELECT
				sum(ifnull(D.COUNTER,0))								TOTAL_COUNTER,
				sum(round(ifnull(D.MONEY,0)*$rate,2))							TOTAL_MONEY,
				sum(if(to_days(curdate())=to_days(D.DATE_STAT),ifnull(D.COUNTER,0),0))			TODAY_COUNTER,
				sum(if(to_days(curdate())-to_days(D.DATE_STAT)=1,ifnull(D.COUNTER,0),0))		YESTERDAY_COUNTER,
				sum(if(to_days(curdate())-to_days(D.DATE_STAT)=2,ifnull(D.COUNTER,0),0))		B_YESTERDAY_COUNTER,
				sum(".($filter_period ? $strSqlPeriod.'ifnull(D.COUNTER,0)'.$strT : 0).")		PERIOD_COUNTER,
				sum(round(if(to_days(curdate())-to_days(D.DATE_STAT)=0,ifnull(D.MONEY,0),0)*$rate,2))	TODAY_MONEY,
				sum(round(if(to_days(curdate())-to_days(D.DATE_STAT)=1,ifnull(D.MONEY,0),0)*$rate,2))	YESTERDAY_MONEY,
				sum(round(if(to_days(curdate())-to_days(D.DATE_STAT)=2,ifnull(D.MONEY,0),0)*$rate,2))	B_YESTERDAY_MONEY,
				sum(round(".($filter_period ? $strSqlPeriod.'ifnull(D.MONEY,0)'.$strT : 0)."*$rate,2))	PERIOD_MONEY
			FROM
				b_stat_event E
				LEFT JOIN b_stat_event_day D ON (D.EVENT_ID = E.ID)
			WHERE
				$strSqlSearch
			HAVING
				'1'='1'
				$strSqlSearch_h
			";
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);
			if($ar = $res->Fetch())
				foreach($ar as $k=>$v)
					$arResult[$k]+=$v;
			$strSql = "
			SELECT
				sum(ifnull(E.COUNTER,0))		TOTAL_COUNTER,
				sum(round(ifnull(E.MONEY,0)*$rate,2))	TOTAL_MONEY
			FROM
				b_stat_event E
			WHERE
				$strSqlSearch
			";
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);
			if($ar = $res->Fetch())
				foreach($ar as $k=>$v)
					$arResult[$k]+=$v;
			$arResult["CURRENCY"]=$view_currency;
			$res = new CDBResult;
			$res->InitFromArray(array($arResult));
		}
		else
		{
			$arResult = array();
			if ($find_group=="event1") $group = "E.EVENT1"; else $group = "E.EVENT2";
			$strSql = "
			SELECT
				$group											GROUPING_KEY,
				$group,
				'".$DB->ForSql($view_currency)."'									CURRENCY,
				".$DB->DateToCharFunction("min(E.DATE_ENTER)")."					DATE_ENTER,
				".$DB->DateToCharFunction("max(D.DATE_LAST)")."						DATE_LAST,
				max(ifnull(D.DATE_LAST,'1980-01-01'))							E_DATE_LAST,
				sum(ifnull(D.COUNTER,0))								TOTAL_COUNTER,
				sum(round(ifnull(D.MONEY,0)*$rate,2))							TOTAL_MONEY,
				sum(if(to_days(curdate())=to_days(D.DATE_STAT),ifnull(D.COUNTER,0),0))			TODAY_COUNTER,
				sum(if(to_days(curdate())-to_days(D.DATE_STAT)=1,ifnull(D.COUNTER,0),0))		YESTERDAY_COUNTER,
				sum(if(to_days(curdate())-to_days(D.DATE_STAT)=2,ifnull(D.COUNTER,0),0))		B_YESTERDAY_COUNTER,
				sum(".($filter_period ? $strSqlPeriod.'ifnull(D.COUNTER,0)'.$strT : 0).")		PERIOD_COUNTER,
				sum(round(if(to_days(curdate())-to_days(D.DATE_STAT)=0,ifnull(D.MONEY,0),0)*$rate,2))	TODAY_MONEY,
				sum(round(if(to_days(curdate())-to_days(D.DATE_STAT)=1,ifnull(D.MONEY,0),0)*$rate,2))	YESTERDAY_MONEY,
				sum(round(if(to_days(curdate())-to_days(D.DATE_STAT)=2,ifnull(D.MONEY,0),0)*$rate,2))	B_YESTERDAY_MONEY,
				sum(round(".($filter_period ? $strSqlPeriod.'ifnull(D.MONEY,0)'.$strT : 0)."*$rate,2))	PERIOD_MONEY
			FROM
				b_stat_event E
			LEFT JOIN b_stat_event_day D ON (D.EVENT_ID = E.ID)
			WHERE
			$strSqlSearch
			GROUP BY $group
			HAVING
				'1'='1'
				$strSqlSearch_h
			$strSqlOrder
			";
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);
			while($ar=$res->Fetch())
				$arResult[$ar["GROUPING_KEY"]] = $ar;
			$strSql = "
			SELECT
				$group							GROUPING_KEY,
				'".$DB->ForSql($view_currency)."'					CURRENCY,
				sum(ifnull(E.COUNTER,0))				COUNTER,
				sum(round(ifnull(E.MONEY,0)*$rate,2))			TOTAL_MONEY,
				".$DB->DateToCharFunction("min(E.DATE_ENTER)")."	DATE_ENTER
			FROM
				b_stat_event E
			WHERE
			$strSqlSearch
			GROUP BY $group
			";
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);
			while($ar=$res->Fetch())
			{
				if(array_key_exists($ar["GROUPING_KEY"], $arResult))
				{
					$arResult[$ar["GROUPING_KEY"]]["TOTAL_COUNTER"] += $ar["COUNTER"];
					$arResult[$ar["GROUPING_KEY"]]["TOTAL_MONEY"] += $ar["MONEY"];
				}
				else
				{
					$arResult[$ar["GROUPING_KEY"]] = array(
						"GROUPING_KEY"			=>$ar["GROUPING_KEY"],
						($find_group=="event1"?"EVENT1":"EVENT2")=>$ar["GROUPING_KEY"],
						"CURRENCY"		=>$ar["CURRENCY"],
						"DATE_ENTER"		=>$ar["DATE_ENTER"],
						"TOTAL_COUNTER"		=>$ar["COUNTER"],
						"TOTAL_MONEY"		=>$ar["MONEY"],
						"TODAY_COUNTER"		=>0,
						"YESTERDAY_COUNTER" 	=>0,
						"B_YESTERDAY_COUNTER"	=>0,
						"PERIOD_COUNTER"	=>0,
						"TODAY_MONEY"		=>0,
						"YESTERDAY_MONEY" 	=>0,
						"B_YESTERDAY_MONEY"	=>0,
						"PERIOD_MONEY"		=>0,
					);/*DATE_LAST,E_DATE_LAST,*/
				}
			}
			$res = new CDBResult;
			$res->InitFromArray($arResult);
		}
		$is_filtered = (IsFiltered($strSqlSearch) || $filter_period || strlen($strSqlSearch_h)>0 || $find_group!="");
		return $res;
	}

	public static function GetSimpleList(&$by, &$order, $arFilter=Array(), &$is_filtered)
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
					if( (strlen($val) <= 0) || ($val === "NOT_REF") )
						continue;
				}
				$match_value_set = array_key_exists($key."_EXACT_MATCH", $arFilter);
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("E.".$key, $val, $match);
						break;
					case "EVENT1":
					case "EVENT2":
					case "NAME":
					case "DESCRIPTION":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.".$key, $val, $match);
						break;
					case "KEYWORDS":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.EVENT1, E.EVENT2, E.DESCRIPTION, E.NAME",$val, $match);
						break;
				}
			}
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$order= ($order!="desc") ? "asc" : "desc";

		if ($by == "s_id")
			$strSqlOrder = "ORDER BY E.ID ".$order;
		elseif ($by == "s_event1")
			$strSqlOrder = "ORDER BY E.EVENT1 ".$order.", E.EVENT2";
		elseif ($by == "s_event2")
			$strSqlOrder = "ORDER BY E.EVENT2 ".$order;
		elseif ($by == "s_name")
			$strSqlOrder = "ORDER BY E.NAME ".$order;
		elseif ($by == "s_description")
			$strSqlOrder = "ORDER BY E.DESCRIPTION ".$order;
		else
		{
			$by = "s_event1";
			$strSqlOrder = "ORDER BY E.EVENT1 ".$order.", E.EVENT2";
		}

		$strSql =	"
			SELECT
				E.ID, E.EVENT1, E.EVENT2, E.NAME, E.DESCRIPTION,
				if (length(E.NAME)>0, E.NAME,
					concat(ifnull(E.EVENT1,''),' / ',ifnull(E.EVENT2,'')))		EVENT
			FROM
				b_stat_event E
			WHERE
			$strSqlSearch
			$strSqlOrder
			LIMIT ".intval(COption::GetOptionString('statistic','RECORDS_LIMIT'))."
		";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch));
		return $res;
	}

	public static function GetDropDownList($strSqlOrder="ORDER BY EVENT1, EVENT2")
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$strSql = "
			SELECT
				ID as REFERENCE_ID,
				concat('(',ifnull(EVENT1,''),' / ',ifnull(EVENT2,''),')',ifnull(NAME,''),' [',ID,']') as REFERENCE
			FROM
				b_stat_event
			$strSqlOrder
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetDynamicList($EVENT_ID, &$by, &$order, &$arMaxMin, $arFilter=Array())
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$EVENT_ID = intval($EVENT_ID);
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
					if( (strlen($val) <= 0) || ($val === "NOT_REF") )
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

		if ($by == "s_date")
			$strSqlOrder = "ORDER BY D.DATE_STAT";
		else
		{
			$by = "s_date";
			$strSqlOrder = "ORDER BY D.DATE_STAT";
		}

		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order = "desc";
		}

		$strSql = "
			SELECT
				".$DB->DateToCharFunction("D.DATE_STAT","SHORT")." DATE_STAT,
				DAYOFMONTH(D.DATE_STAT) DAY,
				MONTH(D.DATE_STAT) MONTH,
				YEAR(D.DATE_STAT) YEAR,
				D.COUNTER
			FROM
				b_stat_event_day D
			WHERE
				D.EVENT_ID = $EVENT_ID
			$strSqlSearch
			$strSqlOrder
		";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		$strSql = "
			SELECT
				max(D.DATE_STAT) DATE_LAST,
				min(D.DATE_STAT) DATE_FIRST,
				DAYOFMONTH(max(D.DATE_STAT)) MAX_DAY,
				MONTH(max(D.DATE_STAT)) MAX_MONTH,
				YEAR(max(D.DATE_STAT)) MAX_YEAR,
				DAYOFMONTH(min(D.DATE_STAT)) MIN_DAY,
				MONTH(min(D.DATE_STAT)) MIN_MONTH,
				YEAR(min(D.DATE_STAT)) MIN_YEAR
			FROM
				b_stat_event_day D
			WHERE
				D.EVENT_ID = $EVENT_ID
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

	public static function GetGraphArray_SQL($strSqlSearch)
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$strSql = "
			SELECT
				".$DB->DateToCharFunction("D.DATE_STAT","SHORT")." DATE_STAT,
				DAYOFMONTH(D.DATE_STAT) DAY,
				MONTH(D.DATE_STAT) MONTH,
				YEAR(D.DATE_STAT) YEAR,
				D.COUNTER,
				D.MONEY,
				D.EVENT_ID,
				E.NAME,
				E.EVENT1,
				E.EVENT2
			FROM
				b_stat_event_day D
			INNER JOIN b_stat_event E ON (E.ID = D.EVENT_ID)
			WHERE
				$strSqlSearch
			ORDER BY
				D.DATE_STAT, D.EVENT_ID
			";
		return $strSql;
	}
}
