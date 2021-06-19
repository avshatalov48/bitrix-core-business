<?php
class CPath
{
	public static function GetList($PARENT_ID = '', $COUNTER_TYPE = 'COUNTER_FULL_PATH', $by = 's_counter', $order = 'desc', $arFilter = [])
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		if ($COUNTER_TYPE!="COUNTER_FULL_PATH")
			$COUNTER_TYPE = "COUNTER";
		$arSqlSearch = Array();

		$counter = "P.".$COUNTER_TYPE;
		$where_counter = "and P.".$COUNTER_TYPE.">0";

		if ($PARENT_ID == '' && $COUNTER_TYPE=="COUNTER")
		{
			$where_parent = "and (P.PARENT_PATH_ID is null or ".$DB->Length("P.PARENT_PATH_ID")."<=0)";
		}
		elseif ($COUNTER_TYPE=="COUNTER")
		{
			$where_parent = "and P.PARENT_PATH_ID = '".$DB->ForSql($PARENT_ID)."'";
		}
		else
		{
			$where_parent = "";
		}

		$from_adv = "";
		$where_adv = "";
		$ADV_EXIST = "N";

		if (is_array($arFilter))
		{
			if ($arFilter["ADV"] <> '')
			{
				$from_adv = " , b_stat_path_adv A ";
				$where_adv = "and A.PATH_ID = P.PATH_ID and A.DATE_STAT = P.DATE_STAT ";
				$ADV_EXIST = "Y";
				if ($arFilter["ADV_DATA_TYPE"]=="B")
				{
					$counter = $DB->IsNull("A.".$COUNTER_TYPE."_BACK","0");
					$where_counter = "and ".$counter.">0";
				}
				elseif ($arFilter["ADV_DATA_TYPE"]=="P")
				{
					$counter = $DB->IsNull("A.".$COUNTER_TYPE,"0");
					$where_counter = "and ".$counter.">0";
				}
				elseif ($arFilter["ADV_DATA_TYPE"]=="S")
				{
					$counter = $DB->IsNull("A.".$COUNTER_TYPE,"0")." + ".$DB->IsNull("A.".$COUNTER_TYPE."_BACK","0");
					$where_counter = "and (".$counter.")>0";
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
					case "PATH_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("P.PATH_ID", $val, $match);
						break;
					case "DATE1":
						if (CheckDateTime($val))
						{
							$arSqlSearch[] = "P.DATE_STAT >= ".$DB->CharToDateFunction($val, "SHORT");
							if ($ADV_EXIST=="Y")
								$arSqlSearch[] = "A.DATE_STAT >= ".$DB->CharToDateFunction($val, "SHORT");
						}
						break;
					case "DATE2":
						if (CheckDateTime($val))
						{
							$arSqlSearch[] = "P.DATE_STAT < ".CStatistics::DBDateAdd($DB->CharToDateFunction($val, "SHORT"), 1);
							if ($ADV_EXIST=="Y")
								$arSqlSearch[] = "A.DATE_STAT < ".CStatistics::DBDateAdd($DB->CharToDateFunction($val, "SHORT"), 1);
						}
						break;
					case "FIRST_PAGE":
					case "LAST_PAGE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("P.".$key,$val,$match,array("/","\\",".","?","#",":"));
						break;
					case "FIRST_PAGE_SITE_ID":
					case "LAST_PAGE_SITE_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("P.".$key, $val, $match);
						break;
					case "FIRST_PAGE_404":
					case "LAST_PAGE_404":
						$arSqlSearch[] = ($val=="Y") ? "P.".$key."='Y'" : "P.".$key."='N'";
						break;
					case "PAGE":
						$arSqlSearch[] = GetFilterQuery("P.PAGES", $val, "Y", array("/","\\",".","?","#",":"));
						break;
					case "PAGE_SITE_ID":
						$arSqlSearch[] = GetFilterQuery("P.PAGES", "[".$val."]", "Y", array("[","]"));
						break;
					case "PAGE_404":
						$arSqlSearch[] = ($val=="Y") ? "P.PAGES like '%ERROR_404:%'" : "P.PAGES not like '%ERROR_404:%'";
						break;
					case "ADV":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("A.ADV_ID",$val,$match);
						break;
					case "STEPS1":
						$arSqlSearch[] = "P.STEPS>='".intval($val)."'";
						break;
					case "STEPS2":
						$arSqlSearch[] = "P.STEPS<='".intval($val)."'";
						break;
				}
			}
		}

		if ($COUNTER_TYPE=="COUNTER")
		{
			$select1 = "P.LAST_PAGE, P.LAST_PAGE_404, P.LAST_PAGE_SITE_ID";
		}
		elseif($COUNTER_TYPE=="COUNTER_FULL_PATH")
		{
			$select1 = "P.PAGES";
		}
		else
		{
			$select1 = "";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		if ($by == "s_last_page" && $COUNTER_TYPE=="COUNTER")				$strSqlOrder = "ORDER BY P.LAST_PAGE";
		elseif ($by == "s_pages" && $COUNTER_TYPE=="COUNTER_FULL_PATH")		$strSqlOrder = "ORDER BY P.PAGES";
		elseif ($by == "s_counter")	$strSqlOrder = "ORDER BY COUNTER";
		else
		{
			$strSqlOrder = "ORDER BY COUNTER desc, ".$select1;
		}

		if ($order != "asc")
		{
			$strSqlOrder .= " desc ";
		}

		$strSql = "
			SELECT /*TOP*/
				P.PATH_ID,
				$select1,
				sum($counter) as COUNTER
			FROM
				b_stat_path P
				$from_adv
			WHERE
			$strSqlSearch
			$where_parent
			$where_adv
			$where_counter
			GROUP BY P.PATH_ID, $select1
			$strSqlOrder
		";

		$res = $DB->Query(CStatistics::DBTopSql($strSql), false, $err_mess.__LINE__);

		return $res;
	}

	public static function GetByID($ID)
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$strSql = "SELECT /*TOP*/ * FROM b_stat_path WHERE PATH_ID = '".$DB->ForSql($ID)."'";
		return $DB->Query(CStatistics::DBTopSql($strSql, 1), false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}
}
