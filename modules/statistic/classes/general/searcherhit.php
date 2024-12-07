<?php
class CSearcherHit
{
	public static function GetList($by = 's_date_hit', $order = 'desc', $arFilter = [])
	{
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
					case "SEARCHER_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("H.".$key, $val, $match);
						break;
					case "URL":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("H.URL", $val, $match, array("/","\\",".","?","#",":"));
						break;
					case "URL_404":
						$arSqlSearch[] = ($val=="Y") ? "H.URL_404='Y'" : "H.URL_404='N'";
						break;
					case "SEARCHER":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("S.NAME",$val,$match);
						break;
					case "DATE1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "H.DATE_HIT >= ".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "H.DATE_HIT < ".CStatistics::DBDateAdd($DB->CharToDateFunction($val, "SHORT"), 1);
						break;
					case "IP":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("H.IP", $val, $match, array("."));
						break;
					case "USER_AGENT":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("H.USER_AGENT", $val, $match);
						break;
					case "SITE_ID":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("H.SITE_ID", $val, $match);
						break;
				}
			}
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		if ($by == "s_id")				$strSqlOrder = "ORDER BY H.ID";
		elseif ($by == "s_site_id")		$strSqlOrder = "ORDER BY H.SITE_ID";
		elseif ($by == "s_date_hit")	$strSqlOrder = "ORDER BY H.DATE_HIT";
		elseif ($by == "s_searcher_id")	$strSqlOrder = "ORDER BY H.SEARCHER_ID";
		elseif ($by == "s_user_agent")	$strSqlOrder = "ORDER BY H.USER_AGENT";
		elseif ($by == "s_ip")			$strSqlOrder = "ORDER BY H.IP";
		elseif ($by == "s_url")			$strSqlOrder = "ORDER BY H.URL ";
		else
		{
			$strSqlOrder = "ORDER BY H.DATE_HIT";
		}

		if ($order != "asc")
		{
			$strSqlOrder .= " desc ";
		}

		$strSql = "
			SELECT /*TOP*/
				H.ID, H.SEARCHER_ID, H.URL, H.URL_404, H.IP, H.USER_AGENT, H.HIT_KEEP_DAYS, H.SITE_ID,
				S.NAME SEARCHER_NAME,
				".$DB->DateToCharFunction("H.DATE_HIT")." DATE_HIT
			FROM
				b_stat_searcher_hit H
			INNER JOIN b_stat_searcher S ON (S.ID = H.SEARCHER_ID)
			WHERE
			".$strSqlSearch."
			".$strSqlOrder."
		";

		$res = $DB->Query(CStatistics::DBTopSql($strSql));

		return $res;
	}
}
