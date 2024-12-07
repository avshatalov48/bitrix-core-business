<?php
class CReferer
{
	public static function GetList($by = 's_id', $order = 'desc', $arFilter = [], $is_filtered = null, &$total = 0, &$grby = '', &$max = 0)
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$group = false;
		$strSqlGroup =  "GROUP BY L.PROTOCOL, L.SITE_NAME, L.URL_FROM, R.HITS, R.SESSIONS";
		$url_from = $DB->Concat("L.PROTOCOL", "L.SITE_NAME", "L.URL_FROM");
		$arSqlSearch = Array();
		$find_group = "";

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
					case "SESSION_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("L.".$key,$val,$match);
						break;
					case "DATE1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "L.DATE_HIT >= ".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "L.DATE_HIT < ".CStatistics::DBDateAdd($DB->CharToDateFunction($val, "SHORT"), 1);
						break;
					case "FROM_PROTOCOL":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("L.PROTOCOL",$val,$match,array("/","\\",":"));
						break;
					case "FROM_DOMAIN":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("L.SITE_NAME",$val,$match,array("."));
						break;
					case "FROM_PAGE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("L.URL_FROM",$val,$match,array("/","\\",".","?","#",":",":"));
						break;
					case "FROM":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery($url_from,$val, $match, array("/","\\",".","?","#",":"), "N", "N");
						break;
					case "TO":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("L.URL_TO",$val,$match,array("/","\\",".","?","#",":"));
						break;
					case "TO_404":
						$arSqlSearch[] = ($val=="Y") ? "L.URL_TO_404='Y'" : "L.URL_TO_404='N'";
						break;
					case "GROUP":
						$group = true;
						if ($val=="S")
						{
							$find_group="S";
							$strSqlGroup = "GROUP BY L.SITE_NAME, R.HITS, R.SESSIONS";
							$url_from = "L.SITE_NAME";
						}
						else $find_group="U";
						break;
					case "SITE_ID":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("L.SITE_ID", $val, $match);
						break;
				}
			}
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$grby = ($find_group=="U" || $find_group=="S") ? $find_group : "";

		if ($grby == '')
		{
			if ($by == "s_id")					$strSqlOrder = " ORDER BY L.ID ";
			elseif ($by == "s_site_id")			$strSqlOrder = " ORDER BY L.SITE_ID ";
			elseif ($by == "s_url_from")		$strSqlOrder = " ORDER BY URL_FROM ";
			elseif ($by == "s_url_to")			$strSqlOrder = " ORDER BY L.URL_TO ";
			elseif ($by == "s_date_hit")		$strSqlOrder = " ORDER BY L.DATE_HIT ";
			elseif ($by == "s_session_id")		$strSqlOrder = " ORDER BY L.SESSION_ID ";
			else
			{
				$strSqlOrder = "ORDER BY L.ID";
			}

			if ($order != "asc")
			{
				$strSqlOrder .= " desc ";
			}

			$strSql = "
				SELECT /*TOP*/
					".$url_from." as URL_FROM,
					L.ID,
					L.SESSION_ID,
					L.SITE_ID,
					".$DB->DateToCharFunction("L.DATE_HIT")." DATE_HIT,
					L.URL_TO,
					L.URL_TO_404
				FROM
					b_stat_referer_list L
				WHERE
				".$strSqlSearch."
				".$strSqlOrder."
			";
		}
		elseif (IsFiltered($strSqlSearch) || $grby=="U")
		{
			if ($by == "s_url_from")			$strSqlOrder = "ORDER BY URL_FROM";
			elseif ($by == "s_quantity")		$strSqlOrder = "ORDER BY QUANTITY";
			elseif ($by == "s_average_hits")	$strSqlOrder = "ORDER BY AVERAGE_HITS";
			else
			{
				$strSqlOrder = "ORDER BY QUANTITY";
			}

			if ($order == "desc")
			{
				$strSqlOrder .= " desc ";
			}

			$strSql = "
				SELECT
					count(L.ID) as COUNTER
				FROM
					b_stat_referer_list L
					LEFT JOIN b_stat_referer R ON (R.ID = L.REFERER_ID)
				WHERE
				".$strSqlSearch."
				".$strSqlGroup."
			";
			$c = $DB->Query($strSql);
			$total = 0;
			$arrCount = array();
			while ($cr = $c->Fetch())
			{
				$total += intval($cr["COUNTER"]);
				$arrCount[] = intval($cr["COUNTER"]);
			}
			if (count($arrCount)>0)
				$max = max($arrCount);
			$strSql = "
				SELECT /*TOP*/
					".$url_from." URL_FROM,
					count(L.ID) QUANTITY,
					(count(L.ID)*100)/$total C_PERCENT,
					R.HITS/R.SESSIONS AVERAGE_HITS
				FROM
					b_stat_referer_list L
					LEFT JOIN b_stat_referer R ON (R.ID = L.REFERER_ID)
				WHERE
				".$strSqlSearch."
				".$strSqlGroup."
				".$strSqlOrder."
			";
		}
		else//if($grby=="S")
		{
			if ($by == "s_url_from")			$strSqlOrder = "ORDER BY URL_FROM";
			elseif ($by == "s_quantity")		$strSqlOrder = "ORDER BY QUANTITY";
			elseif ($by == "s_average_hits")	$strSqlOrder = "ORDER BY AVERAGE_HITS";
			else
			{
				$strSqlOrder = "ORDER BY QUANTITY";
			}

			if ($order == "desc")
			{
				$strSqlOrder .= " desc ";
			}

			$strSql = "SELECT sum(R.SESSIONS) TOTAL, max(R.SESSIONS) MAX FROM b_stat_referer R";
			$c = $DB->Query($strSql);
			$cr = $c->Fetch();
			$total = intval($cr["TOTAL"]);
			$max = intval($cr["MAX"]);
			$strSql = "
				SELECT /*TOP*/
					R.SITE_NAME URL_FROM,
					sum(R.SESSIONS) QUANTITY,
					(sum(R.SESSIONS)*100)/$total C_PERCENT,
					sum(R.HITS)/sum(R.SESSIONS) AVERAGE_HITS
				FROM
					b_stat_referer R
				GROUP BY R.SITE_NAME
				".$strSqlOrder."
			";
		}

		$res = $DB->Query(CStatistics::DBTopSql($strSql));

		return $res;
	}
}
