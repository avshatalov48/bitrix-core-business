<?php
class CAllGuest
{
	public static function GetList($by = 's_last_date', $order = 'desc', $arFilter = [])
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$arSqlSearch = Array();

		$bGroup = false;
		$arrGroup = array(
			"G.ID" => true,
			"G.C_EVENTS" => true,
			"G.FIRST_SITE_ID" => true,
			"G.LAST_SITE_ID" => true,
			"G.SESSIONS" => true,
			"G.HITS" => true,
			"G.FAVORITES" => true,
			"G.FIRST_URL_FROM" => true,
			"G.FIRST_URL_TO" => true,
			"G.FIRST_URL_TO_404" => true,
			"G.FIRST_ADV_ID" => true,
			"G.FIRST_REFERER1" => true,
			"G.FIRST_REFERER2" => true,
			"G.FIRST_REFERER3" => true,
			"G.LAST_ADV_ID" => true,
			"G.LAST_ADV_BACK" => true,
			"G.LAST_REFERER1" => true,
			"G.LAST_REFERER2" => true,
			"G.LAST_REFERER3" => true,
			"G.LAST_USER_ID" => true,
			"G.LAST_USER_AUTH" => true,
			"G.LAST_URL_LAST" => true,
			"G.LAST_URL_LAST_404" => true,
			"G.LAST_USER_AGENT" => true,
			"G.LAST_IP" => true,
			"G.LAST_LANGUAGE" => true,
			"G.LAST_COUNTRY_ID" => true,
			"G.LAST_CITY_ID" => true,
			"G.FIRST_DATE" => true,
			"G.LAST_DATE" => true,
			"G.FIRST_SESSION_ID" => true,
			"G.LAST_SESSION_ID" => true,
			"CITY.REGION" => true,
			"CITY.NAME" => true,
		);
		$from0 = $from1 = $from2 = "";
		$select0 = $select1 = "";

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
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("G.ID",$val,$match);
						break;
					case "REGISTERED":
						if ($val=="Y")
							$arSqlSearch[] = "G.LAST_USER_ID>0 and G.LAST_USER_ID is not null";
						elseif ($val=="N")
							$arSqlSearch[] = "G.LAST_USER_ID<=0 or G.LAST_USER_ID is null";
						break;
					case "FIRST_DATE1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "G.FIRST_DATE >= ".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "FIRST_DATE2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "G.FIRST_DATE < ".CStatistics::DBDateAdd($DB->CharToDateFunction($val, "SHORT"), 1);
						break;
					case "LAST_DATE1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "G.LAST_DATE >= ".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "LAST_DATE2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "G.LAST_DATE < ".CStatistics::DBDateAdd($DB->CharToDateFunction($val, "SHORT"), 1);
						break;
					case "PERIOD_DATE1":
						ResetFilterLogic();
						if (CheckDateTime($val))
						{
							$arSqlSearch[] = "S.DATE_FIRST >= ".$DB->CharToDateFunction($val, "SHORT");
							$from0 = " INNER JOIN b_stat_session S ON (S.GUEST_ID = G.ID) ";
							$select0 = "count(S.ID) as SESS,";
							$bGroup = true;
						}
						break;
					case "PERIOD_DATE2":
						ResetFilterLogic();
						if (CheckDateTime($val))
						{
							$arSqlSearch[] = "S.DATE_LAST < ".CStatistics::DBDateAdd($DB->CharToDateFunction($val, "SHORT"), 1);
							$from0 = " INNER JOIN b_stat_session S ON (S.GUEST_ID = G.ID) ";
							$select0 = "count(S.ID) as SESS,";
							$bGroup = true;
						}
						break;
					case "SITE_ID":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("G.LAST_SITE_ID, G.FIRST_SITE_ID", $val, $match);
						break;
					case "LAST_SITE_ID":
					case "FIRST_SITE_ID":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("G.".$key, $val, $match);
						break;
					case "URL":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("G.FIRST_URL_FROM,G.FIRST_URL_TO,G.LAST_URL_LAST", $val, $match, array("/","\\",".","?","#",":"));
						break;
					case "URL_404":
						if ($val=="Y")
							$arSqlSearch[] = "G.FIRST_URL_TO_404='Y' or	G.LAST_URL_LAST_404='Y'";
						elseif ($val=="N")
							$arSqlSearch[] = "G.FIRST_URL_TO_404='N' and G.LAST_URL_LAST_404='N'";
						break;
					case "USER_AGENT":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("G.LAST_USER_AGENT", $val, $match);
						break;
					case "ADV":
						if ($val=="Y")
						{
							$arSqlSearch[] = "(
									G.FIRST_ADV_ID>0 and
									G.FIRST_ADV_ID is not null and
									G.FIRST_REFERER1<>'NA' and G.FIRST_REFERER2<>'NA'
								or
									G.LAST_ADV_ID>0 and
									G.LAST_ADV_ID is not null and
									G.LAST_REFERER1<>'NA' and G.LAST_REFERER2<>'NA'
								)";
						}
						elseif ($val=="N")
						{
							$arSqlSearch[] = "((
										G.FIRST_ADV_ID<=0 or
										G.FIRST_ADV_ID is null or
										(G.FIRST_REFERER1='NA' and G.FIRST_REFERER2='NA')
									) and (
										G.LAST_ADV_ID<=0 or
										G.LAST_ADV_ID is null or
										(G.LAST_REFERER1='NA' and G.LAST_REFERER2='NA')
									))";
						}
						break;
					case "ADV_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("G.FIRST_ADV_ID,G.LAST_ADV_ID", $val, $match);
						break;
					case "REFERER1":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("G.FIRST_REFERER1,G.LAST_REFERER1", $val, $match);
						break;
					case "REFERER2":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("G.FIRST_REFERER2,G.LAST_REFERER2", $val, $match);
						break;
					case "REFERER3":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("G.FIRST_REFERER3,G.LAST_REFERER3", $val, $match);
						break;
					case "EVENTS1":
						$arSqlSearch[] = "G.C_EVENTS>='".intval($val)."'";
						break;
					case "EVENTS2":
						$arSqlSearch[] = "G.C_EVENTS<='".intval($val)."'";
						break;
					case "SESS1":
						$arSqlSearch[] = "G.SESSIONS>='".intval($val)."'";
						break;
					case "SESS2":
						$arSqlSearch[] = "G.SESSIONS<='".intval($val)."'";
						break;
					case "HITS1":
						$arSqlSearch[] = "G.HITS>='".intval($val)."'";
						break;
					case "HITS2":
						$arSqlSearch[] = "G.HITS<='".intval($val)."'";
						break;
					case "FAVORITES":
						if ($val=="Y")
							$arSqlSearch[] = "G.FAVORITES='Y'";
						elseif ($val=="N")
							$arSqlSearch[] = "G.FAVORITES<>'Y'";
						break;
					case "IP":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("G.LAST_IP",$val,$match,array("."));
						break;
					case "LANG":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("G.LAST_LANGUAGE", $val, $match);
						break;
					case "COUNTRY_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("G.LAST_COUNTRY_ID", $val, $match);
						break;
					case "COUNTRY":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("C.NAME", $val, $match);
						$select1 .= " , C.NAME LAST_COUNTRY_NAME ";
						$from2 = " LEFT JOIN b_stat_country C ON (C.ID = G.LAST_COUNTRY_ID) ";
						$arrGroup["C.NAME"] = true;
						$bGroup = true;
						break;
					case "REGION":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("CITY.REGION", $val, $match);
						break;
					case "CITY_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("G.LAST_CITY_ID", $val, $match);
						break;
					case "CITY":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("CITY.NAME", $val, $match);
						break;
					case "USER":
						if(COption::GetOptionInt("statistic", "dbnode_id") <= 0)
						{
							$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
							$arSqlSearch[] = $DB->IsNull("G.LAST_USER_ID","0").">0";
							$arSqlSearch[] = GetFilterQuery("G.LAST_USER_ID,A.LOGIN,A.LAST_NAME,A.NAME", $val, $match);
							$select1 .= ", ".$DB->Concat($DB->IsNull("A.NAME","''"), "' '", $DB->IsNull("A.LAST_NAME","''"))." USER_NAME, A.LOGIN";
							$from1 = "LEFT JOIN  b_user A ON (A.ID = G.LAST_USER_ID) ";
							$arrGroup["A.NAME"] = true;
							$arrGroup["A.LAST_NAME"] = true;
							$arrGroup["A.LOGIN"] = true;
							$bGroup = true;
						}
						break;
					case "USER_ID":
						if(COption::GetOptionInt("statistic", "dbnode_id") <= 0)
						{
							$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
							$arSqlSearch[] = $DB->IsNull("G.LAST_USER_ID","0").">0";
							$arSqlSearch[] = GetFilterQuery("G.LAST_USER_ID", $val, $match);
							$select1 .= ", ".$DB->Concat($DB->IsNull("A.NAME","''"), "' '", $DB->IsNull("A.LAST_NAME","''"))." USER_NAME, A.LOGIN";
							$from1 = "LEFT JOIN  b_user A ON (A.ID = G.LAST_USER_ID) ";
							$arrGroup["A.NAME"] = true;
							$arrGroup["A.LAST_NAME"] = true;
							$arrGroup["A.LOGIN"] = true;
							$bGroup = true;
						}
						break;
				}
			}
		}

		if ($by == "s_id")					$strSqlOrder = "ORDER BY G.ID";
		elseif ($by == "s_first_site_id")	$strSqlOrder = "ORDER BY G.FIRST_SITE_ID";
		elseif ($by == "s_last_site_id")	$strSqlOrder = "ORDER BY G.LAST_SITE_ID";
		elseif ($by == "s_events")			$strSqlOrder = "ORDER BY G.C_EVENTS";
		elseif ($by == "s_sessions")		$strSqlOrder = "ORDER BY G.SESSIONS";
		elseif ($by == "s_hits")			$strSqlOrder = "ORDER BY G.HITS";
		elseif ($by == "s_first_date")		$strSqlOrder = "ORDER BY G.FIRST_DATE";
		elseif ($by == "s_first_url_from")	$strSqlOrder = "ORDER BY G.FIRST_URL_FROM";
		elseif ($by == "s_first_url_to")	$strSqlOrder = "ORDER BY G.FIRST_URL_TO";
		elseif ($by == "s_first_adv_id")	$strSqlOrder = "ORDER BY G.FIRST_ADV_ID";
		elseif ($by == "s_last_date")		$strSqlOrder = "ORDER BY ".CStatistics::DBFirstDate("G.LAST_DATE");
		elseif ($by == "s_last_user_id")	$strSqlOrder = "ORDER BY G.LAST_USER_ID";
		elseif ($by == "s_last_url_last")	$strSqlOrder = "ORDER BY G.LAST_URL_LAST";
		elseif ($by == "s_last_user_agent")	$strSqlOrder = "ORDER BY G.LAST_USER_AGENT";
		elseif ($by == "s_last_ip")			$strSqlOrder = "ORDER BY G.LAST_IP";
		elseif ($by == "s_last_adv_id")		$strSqlOrder = "ORDER BY G.LAST_ADV_ID";
		elseif ($by == "s_last_country_id")	$strSqlOrder = "ORDER BY G.LAST_COUNTRY_ID";
		elseif ($by == "s_last_region_name")	$strSqlOrder = "ORDER BY CITY.REGION";
		elseif ($by == "s_last_city_id")	$strSqlOrder = "ORDER BY G.LAST_CITY_ID";
		else
		{
			$strSqlOrder = "ORDER BY ".CStatistics::DBFirstDate("G.LAST_DATE");
		}

		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
		}

		$strSqlGroup = $bGroup? "GROUP BY ".implode(", ", array_keys($arrGroup)): "";

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT /*TOP*/
				".$select0."
				G.ID, G.FIRST_SITE_ID, G.FIRST_SESSION_ID,
				G.LAST_SESSION_ID, G.LAST_SITE_ID,
				G.C_EVENTS, G.SESSIONS, G.HITS,  G.FAVORITES,
				G.FIRST_URL_FROM, G.FIRST_URL_TO, G.FIRST_URL_TO_404,
				G.FIRST_ADV_ID, G.FIRST_REFERER1, G.FIRST_REFERER2, G.FIRST_REFERER3,
				G.LAST_ADV_ID, G.LAST_ADV_BACK, G.LAST_REFERER1, G.LAST_REFERER2, G.LAST_REFERER3,
				G.LAST_USER_ID, G.LAST_USER_AUTH, G.LAST_URL_LAST, G.LAST_URL_LAST_404,
				G.LAST_USER_AGENT, G.LAST_IP, G.LAST_LANGUAGE, G.LAST_COUNTRY_ID,
				CITY.REGION as LAST_REGION_NAME,
				G.LAST_CITY_ID, CITY.NAME as LAST_CITY_NAME,
				".$DB->DateToCharFunction("G.FIRST_DATE")." FIRST_DATE,
				".$DB->DateToCharFunction("G.LAST_DATE")." LAST_DATE
				".$select1."
			FROM
				b_stat_guest G
			".$from0."
			".$from1."
			".$from2."
				LEFT JOIN b_stat_city CITY ON (CITY.ID = G.LAST_CITY_ID)
			WHERE
			".$strSqlSearch."
			".$strSqlGroup."
			".$strSqlOrder."
		";

		$res = $DB->Query(CStatistics::DBTopSql($strSql));

		return $res;
	}

	public static function GetByID($ID)
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$ID = intval($ID);

		$res = $DB->Query("
			SELECT
				G.*,
				".$DB->DateToCharFunction("G.FIRST_DATE")." FIRST_DATE,
				".$DB->DateToCharFunction("G.LAST_DATE")." LAST_DATE,
				".CStatistics::DBDateDiff("FS.DATE_LAST","FS.DATE_FIRST")." FSESSION_TIME,
				".CStatistics::DBDateDiff("LS.DATE_LAST","LS.DATE_FIRST")." LSESSION_TIME,
				FS.HITS FSESSION_HITS,
				LS.HITS LSESSION_HITS,
				C.NAME COUNTRY_NAME,
				CITY.REGION REGION_NAME,
				CITY.NAME CITY_NAME,
				G.LAST_CITY_INFO
			FROM
				b_stat_guest G
				INNER JOIN b_stat_country C ON (C.ID = G.LAST_COUNTRY_ID)
				LEFT JOIN b_stat_session FS ON (FS.ID = G.FIRST_SESSION_ID)
				LEFT JOIN b_stat_session LS ON (LS.ID = G.LAST_SESSION_ID)
				LEFT JOIN b_stat_city CITY ON (CITY.ID = G.LAST_CITY_ID)
			WHERE
				G.ID = '$ID'
		");

		$res = new CStatResult($res);
		return $res;
	}
}
