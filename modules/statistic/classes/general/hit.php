<?php
class CHit
{
	public static function GetList(&$by, &$order, $arFilter=Array(), &$is_filtered)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$arSqlSearch = Array();
		$select = "";
		$from1 = $from2 = "";

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
					case "GUEST_ID":
					case "SESSION_ID":
					case "STOP_LIST_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("H.".$key, $val, $match);
						break;
					case "URL":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("H.URL", $val, $match, array("/","\\",".","?","#",":"));
						break;
					case "URL_404":
					case "NEW_GUEST":
						$arSqlSearch[] = ($val=="Y") ? "H.".$key."='Y'" : "H.".$key."='N'";
						break;
					case "REGISTERED":
						$arSqlSearch[] = ($val=="Y") ? "H.USER_ID>0" : "(H.USER_ID<=0 or H.USER_ID is null)";
						break;
					case "DATE_1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "H.DATE_HIT >= ".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "H.DATE_HIT < ".CStatistics::DBDateAdd($DB->CharToDateFunction($val, "SHORT"), 1);
						break;
					case "IP":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("H.IP",$val,$match,array("."));
						break;
					case "USER_AGENT":
					case "COUNTRY_ID":
					case "CITY_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("H.".$key, $val, $match);
						break;
					case "COOKIE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("H.COOKIES",$val,$match);
						break;
					case "USER":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = $DB->IsNull("H.USER_ID","0").">0";
						$arSqlSearch[] = GetFilterQuery("H.USER_ID,A.LOGIN,A.LAST_NAME,A.NAME", $val, $match);
						$select = ", A.LOGIN, ".$DB->Concat($DB->IsNull("A.NAME","''"), "' '", $DB->IsNull("A.LAST_NAME","''"))." USER_NAME";
						$from1 = "LEFT JOIN b_user A ON (A.ID = H.USER_ID)";
						break;
					case "COUNTRY":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("C.NAME", $val, $match);
						$from2 = "INNER JOIN b_stat_country C ON (C.ID = H.COUNTRY_ID)";
						break;
					case "REGION":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("CITY.REGION", $val, $match);
						break;
					case "CITY":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("CITY.NAME", $val, $match);
						break;
					case "STOP":
						$arSqlSearch[] = ($val=="Y") ? "H.STOP_LIST_ID>0" : "(H.STOP_LIST_ID<=0 or H.STOP_LIST_ID is null)";
						break;
					case "SITE_ID":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("H.SITE_ID", $val, $match);
						break;
				}
			}
		}

		if ($by == "s_id")				$strSqlOrder = "ORDER BY H.ID";
		elseif ($by == "s_site_id")		$strSqlOrder = "ORDER BY H.SITE_ID";
		elseif ($by == "s_session_id")	$strSqlOrder = "ORDER BY H.SESSION_ID";
		elseif ($by == "s_date_hit")	$strSqlOrder = "ORDER BY H.DATE_HIT";
		elseif ($by == "s_user_id")		$strSqlOrder = "ORDER BY H.USER_ID";
		elseif ($by == "s_guest_id")	$strSqlOrder = "ORDER BY H.GUEST_ID";
		elseif ($by == "s_ip")			$strSqlOrder = "ORDER BY H.IP";
		elseif ($by == "s_url")			$strSqlOrder = "ORDER BY H.URL ";
		elseif ($by == "s_country_id")	$strSqlOrder = "ORDER BY H.COUNTRY_ID ";
		elseif ($by == "s_region_name")	$strSqlOrder = "ORDER BY CITY.REGION ";
		elseif ($by == "s_city_id")	$strSqlOrder = "ORDER BY H.CITY_ID ";
		else
		{
			$by = "s_id";
			$strSqlOrder = "ORDER BY H.ID";
		}
		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT /*TOP*/
				H.ID,
				H.SESSION_ID,
				H.GUEST_ID,
				H.NEW_GUEST,
				H.USER_ID,
				H.USER_AUTH,
				H.URL,
				H.URL_404,
				H.URL_FROM,
				H.IP,
				H.METHOD,
				H.COOKIES,
				H.USER_AGENT,
				H.STOP_LIST_ID,
				H.COUNTRY_ID,
				H.CITY_ID,
				CITY.REGION REGION_NAME,
				CITY.NAME CITY_NAME,
				H.SITE_ID,
				".$DB->DateToCharFunction("H.DATE_HIT")." DATE_HIT
				".$select."
			FROM
				b_stat_hit H
				LEFT JOIN b_stat_city CITY ON (CITY.ID = H.CITY_ID)
			".$from1."
			".$from2."
			WHERE
			".$strSqlSearch."
			".$strSqlOrder."
		";

		$res = $DB->Query(CStatistics::DBTopSql($strSql), false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch));
		return $res;
	}

	public static function GetByID($ID)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$ID = intval($ID);
		$res = $DB->Query("
			SELECT
				H.*,
				".$DB->DateToCharFunction("H.DATE_HIT")." DATE_HIT,
				C.NAME COUNTRY_NAME,
				CITY.REGION REGION_NAME,
				CITY.NAME CITY_NAME
			FROM
				b_stat_hit H
				INNER JOIN b_stat_country C ON (C.ID = H.COUNTRY_ID)
				LEFT JOIN b_stat_city CITY ON (CITY.ID = H.CITY_ID)
			WHERE
				H.ID = '$ID'
		", false, $err_mess.__LINE__);

		$res = new CStatResult($res);
		return $res;
	}
}
