<?php

class CSession
{
	public static function GetAttentiveness($DATE_STAT, $SITE_ID=false)
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		if ($SITE_ID!==false)
			$str = " and S.FIRST_SITE_ID = '".$DB->ForSql($SITE_ID,2)."' ";
		else
			$str = "";

		$strSql = "
			SELECT
				sum(UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST))/count(S.ID)		AM_AVERAGE_TIME,
				sum(if(UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)<60,1,0))		AM_1,
				sum(if(UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)>=60
				and UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)<180,1,0))		AM_1_3,
				sum(if(UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)>=180
				and UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)<360,1,0))		AM_3_6,
				sum(if(UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)>=360
				and UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)<540,1,0))		AM_6_9,
				sum(if(UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)>=540
				and UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)<720,1,0))		AM_9_12,
				sum(if(UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)>=720
				and UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)<900,1,0))		AM_12_15,
				sum(if(UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)>=900
				and UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)<1080,1,0))		AM_15_18,
				sum(if(UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)>=1080
				and UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)<1260,1,0))		AM_18_21,
				sum(if(UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)>=1260
				and UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)<1440,1,0))		AM_21_24,
				sum(if(UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST)>=1440,1,0))	AM_24,

				sum(S.HITS)/count(S.ID)						AH_AVERAGE_HITS,
				sum(if(S.HITS<=1, 1, 0))					AH_1,
				sum(if(S.HITS>=2 and S.HITS<=5, 1, 0))		AH_2_5,
				sum(if(S.HITS>=6 and S.HITS<=9, 1, 0))		AH_6_9,
				sum(if(S.HITS>=10 and S.HITS<=13, 1, 0))	AH_10_13,
				sum(if(S.HITS>=14 and S.HITS<=17, 1, 0))	AH_14_17,
				sum(if(S.HITS>=18 and S.HITS<=21, 1, 0))	AH_18_21,
				sum(if(S.HITS>=22 and S.HITS<=25, 1, 0))	AH_22_25,
				sum(if(S.HITS>=26 and S.HITS<=29, 1, 0))	AH_26_29,
				sum(if(S.HITS>=30 and S.HITS<=33, 1, 0))	AH_30_33,
				sum(if(S.HITS>=34, 1, 0))					AH_34
			FROM
				b_stat_session S
			WHERE
				S.DATE_STAT = cast(".$DB->CharToDateFunction($DATE_STAT, "SHORT")." as date)
			$str
			";

		$rs = $DB->Query($strSql);
		$ar = $rs->Fetch();
		$arKeys = array_keys($ar);
		foreach($arKeys as $key)
		{
			if ($key=="AM_AVERAGE_TIME" || $key=="AH_AVERAGE_HITS")
			{
				$ar[$key] = (float) $ar[$key];
				$ar[$key] = round($ar[$key],2);
			}
			else
			{
				$ar[$key] = intval($ar[$key]);
			}
		}
		return $ar;
	}

	public static function GetList($by = 's_id', $order = 'desc', $arFilter = [])
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$arSqlSearch = Array();
		$select = "";
		$from1 = "";
		$from2 = "";
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
					case "GUEST_ID":
					case "ADV_ID":
					case "STOP_LIST_ID":
					case "USER_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("S.".$key,$val,$match);
						break;
					case "COUNTRY_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("S.COUNTRY_ID",$val,$match);
						break;
					case "CITY_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("S.CITY_ID",$val,$match);
						break;
					case "DATE_START_1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "S.DATE_FIRST>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_START_2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "S.DATE_FIRST<".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
						break;
					case "DATE_END_1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "S.DATE_LAST>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_END_2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "S.DATE_LAST<".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
						break;
					case "IP":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("S.IP_LAST",$val,$match,array("."));
						break;
					case "REGISTERED":
						$arSqlSearch[] = ($val=="Y") ? "S.USER_ID>0" : "(S.USER_ID<=0 or S.USER_ID is null)";
						break;
					case "EVENTS1":
						$arSqlSearch[] = "S.C_EVENTS>='".intval($val)."'";
						break;
					case "EVENTS2":
						$arSqlSearch[] = "S.C_EVENTS<='".intval($val)."'";
						break;
					case "HITS1":
						$arSqlSearch[] = "S.HITS>='".intval($val)."'";
						break;
					case "HITS2":
						$arSqlSearch[] = "S.HITS<='".intval($val)."'";
						break;
					case "ADV":
						if ($val=="Y")
							$arSqlSearch[] = "(S.ADV_ID>0 and S.ADV_ID is not null)";
						elseif ($val=="N")
							$arSqlSearch[] = "(S.ADV_ID<=0 or S.ADV_ID is null)";
						break;
					case "REFERER1":
					case "REFERER2":
					case "REFERER3":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("S.".$key, $val, $match);
						break;
					case "USER_AGENT":
						$val = preg_replace("/[\n\r]+/", " ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("S.USER_AGENT", $val, $match);
						break;
					case "STOP":
						$arSqlSearch[] = ($val=="Y") ? "S.STOP_LIST_ID>0" : "(S.STOP_LIST_ID<=0 or S.STOP_LIST_ID is null)";
						break;
					case "COUNTRY":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("C.NAME", $val, $match);
						$from2 = "INNER JOIN b_stat_country C ON (C.ID = S.COUNTRY_ID)";
						break;
					case "REGION":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("CITY.REGION", $val, $match);
						break;
					case "CITY":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("CITY.NAME", $val, $match);
						break;
					case "URL_TO":
					case "URL_LAST":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("S.".$key,$val,$match,array("/","\\",".","?","#",":"));
						break;
					case "ADV_BACK":
					case "NEW_GUEST":
					case "FAVORITES":
					case "URL_LAST_404":
					case "URL_TO_404":
					case "USER_AUTH":
						$arSqlSearch[] = ($val=="Y") ? "S.".$key."='Y'" : "S.".$key."='N'";
						break;
					case "USER":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = "ifnull(S.USER_ID,0)>0";
						$arSqlSearch[] = GetFilterQuery("S.USER_ID,A.LOGIN,A.LAST_NAME,A.NAME", $val, $match);
						$from1 = "LEFT JOIN b_user A ON (A.ID = S.USER_ID)";
						$select = " , A.LOGIN, concat(ifnull(A.NAME,''),' ',ifnull(A.LAST_NAME,'')) USER_NAME";
						break;
					case "LAST_SITE_ID":
					case "FIRST_SITE_ID":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("S.".$key, $val, $match);
						break;
				}
			}
		}

		if ($by == "s_id")					$strSqlOrder = "ORDER BY S.ID";
		elseif ($by == "s_last_site_id")	$strSqlOrder = "ORDER BY S.LAST_SITE_ID";
		elseif ($by == "s_first_site_id")	$strSqlOrder = "ORDER BY S.FIRST_SITE_ID";
		elseif ($by == "s_date_first")		$strSqlOrder = "ORDER BY S.DATE_FIRST";
		elseif ($by == "s_date_last")		$strSqlOrder = "ORDER BY S.DATE_LAST";
		elseif ($by == "s_user_id")			$strSqlOrder = "ORDER BY S.USER_ID";
		elseif ($by == "s_guest_id")		$strSqlOrder = "ORDER BY S.GUEST_ID";
		elseif ($by == "s_ip")				$strSqlOrder = "ORDER BY S.IP_LAST";
		elseif ($by == "s_hits")			$strSqlOrder = "ORDER BY S.HITS ";
		elseif ($by == "s_events")			$strSqlOrder = "ORDER BY S.C_EVENTS ";
		elseif ($by == "s_adv_id")			$strSqlOrder = "ORDER BY S.ADV_ID ";
		elseif ($by == "s_country_id")		$strSqlOrder = "ORDER BY S.COUNTRY_ID ";
		elseif ($by == "s_region_name")		$strSqlOrder = "ORDER BY CITY.REGION ";
		elseif ($by == "s_city_id")		$strSqlOrder = "ORDER BY S.CITY_ID ";
		elseif ($by == "s_url_last")		$strSqlOrder = "ORDER BY S.URL_LAST ";
		elseif ($by == "s_url_to")			$strSqlOrder = "ORDER BY S.URL_TO ";
		else
		{
			$strSqlOrder = "ORDER BY S.ID";
		}

		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				S.ID,
				S.GUEST_ID,
				S.NEW_GUEST,
				S.USER_ID,
				S.USER_AUTH,
				S.C_EVENTS,
				S.HITS,
				S.FAVORITES,
				S.URL_FROM,
				S.URL_TO,
				S.URL_TO_404,
				S.URL_LAST,
				S.URL_LAST_404,
				S.USER_AGENT,
				S.IP_FIRST,
				S.IP_LAST,
				S.FIRST_HIT_ID,
				S.LAST_HIT_ID,
				S.PHPSESSID,
				S.ADV_ID,
				S.ADV_BACK,
				S.REFERER1,
				S.REFERER2,
				S.REFERER3,
				S.STOP_LIST_ID,
				S.COUNTRY_ID,
				CITY.REGION REGION_NAME,
				S.CITY_ID,
				CITY.NAME CITY_NAME,
				S.FIRST_SITE_ID,
				S.LAST_SITE_ID,
				UNIX_TIMESTAMP(S.DATE_LAST)-UNIX_TIMESTAMP(S.DATE_FIRST) SESSION_TIME,
				".$DB->DateToCharFunction("S.DATE_FIRST")." DATE_FIRST,
				".$DB->DateToCharFunction("S.DATE_LAST")." DATE_LAST
				$select
			FROM
				b_stat_session S
			$from1
			$from2
				LEFT JOIN b_stat_city CITY ON (CITY.ID = S.CITY_ID)
			WHERE
			$strSqlSearch
			$strSqlOrder
			LIMIT ".intval(COption::GetOptionString('statistic','RECORDS_LIMIT'))."
			";

		$res = $DB->Query($strSql);

		return $res;
	}

	public static function GetByID($ID)
	{
		$statDB = CDatabase::GetModuleConnection('statistic');
		$ID = intval($ID);

		$res = $statDB->Query("
			SELECT
				S.*,
				UNIX_TIMESTAMP(S.DATE_LAST) - UNIX_TIMESTAMP(S.DATE_FIRST) SESSION_TIME,
				".$statDB->DateToCharFunction("S.DATE_FIRST")." DATE_FIRST,
				".$statDB->DateToCharFunction("S.DATE_LAST")." DATE_LAST,
				C.NAME COUNTRY_NAME,
				CITY.REGION REGION_NAME,
				CITY.NAME CITY_NAME
			FROM
				b_stat_session S
				INNER JOIN b_stat_country C ON (C.ID = S.COUNTRY_ID)
				LEFT JOIN b_stat_city CITY ON (CITY.ID = S.CITY_ID)
			WHERE
				S.ID = ".$ID."
		");

		$res = new CStatResult($res);
		return $res;
	}
}
