<?php
class CPhrase
{
	public static function GetList($by = 's_id', $order = 'desc', $arFilter = [], $is_filtered = null, &$total = 0, &$grby = '', &$max = 0)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$group = false;
		$s = "S.NAME as SEARCHER_NAME, S.ID as SEARCHER_ID";
		$strSqlGroup =  "GROUP BY S.ID, S.NAME, S.PHRASES_HITS, S.PHRASES";
		$arSqlSearch = Array("PH.SEARCHER_ID <> 1");
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
					case "SEARCHER_ID":
					case "REFERER_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("PH.".$key,$val,$match);
						break;
					case "SEARCHER_ID_STR":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("S.ID",$val,$match);
						break;
					case "SEARCHER":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("S.NAME", $val, $match);
						break;
					case "DATE1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "PH.DATE_HIT >= ".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "PH.DATE_HIT < ".CStatistics::DBDateAdd($DB->CharToDateFunction($val, "SHORT"), 1);
						break;
					case "PHRASE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						if($match == "N")
							$val = '"'.trim($val, '"').'"';
						$arSqlSearch[] = GetFilterQuery("PH.PHRASE", $val, $match);
						break;
					case "TO":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("PH.URL_TO",$val,$match,array("/","\\",".","?","#",":"));
						break;
					case "TO_404":
						$arSqlSearch[] = ($val=="Y") ? "PH.URL_TO_404='Y'" : "PH.URL_TO_404='N'";
						break;
					case "GROUP":
						$group = true;
						if ($val=="P")
						{
							$find_group="P";
							$strSqlGroup =  " GROUP BY PH.PHRASE ";
							$s = " PH.PHRASE ";
						}
						else $find_group="S";
						break;
					case "SITE_ID":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("PH.SITE_ID", $val, $match);
						break;
				}
			}
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$grby = ($find_group=="P" || $find_group=="S") ? $find_group : "";

		if ($grby == '')
		{
			if ($by == "s_id")					$strSqlOrder = "ORDER BY PH.ID";
			elseif ($by == "s_site_id")			$strSqlOrder = "ORDER BY PH.SITE_ID";
			elseif ($by == "s_phrase")			$strSqlOrder = "ORDER BY PH.PHRASE";
			elseif ($by == "s_searcher_id")		$strSqlOrder = "ORDER BY PH.SEARCHER_ID";
			elseif ($by == "s_referer_id")		$strSqlOrder = "ORDER BY PH.REFERER_ID";
			elseif ($by == "s_date_hit")		$strSqlOrder = "ORDER BY PH.DATE_HIT";
			elseif ($by == "s_url_to")			$strSqlOrder = "ORDER BY PH.URL_TO";
			elseif ($by == "s_session_id")		$strSqlOrder = "ORDER BY PH.SESSION_ID";
			else
			{
				$strSqlOrder = "ORDER BY PH.ID";
			}

			if ($order!="asc")
			{
				$strSqlOrder .= " desc ";
			}

			$strSql = "
				SELECT /*TOP*/
					PH.ID,
					PH.PHRASE,
					PH.SESSION_ID,
					PH.SEARCHER_ID,
					PH.URL_TO,
					PH.URL_TO_404,
					PH.REFERER_ID,
					PH.SITE_ID,
					".$DB->DateToCharFunction("PH.DATE_HIT")." DATE_HIT,
					S.NAME SEARCHER_NAME
				FROM
					b_stat_phrase_list PH
				INNER JOIN b_stat_searcher S ON (S.ID = PH.SEARCHER_ID)
				WHERE
				".$strSqlSearch."
				".$strSqlOrder."
			";
		}
		elseif (IsFiltered($strSqlSearch) || $grby=="P")
		{
			if ($by == "s_phrase" && $grby=="P")			$strSqlOrder = "ORDER BY PH.PHRASE";
			elseif ($by == "s_searcher_id" && $grby=="S")	$strSqlOrder = "ORDER BY PH.SEARCHER_ID";
			elseif ($by == "s_quantity")					$strSqlOrder = "ORDER BY QUANTITY";
			else
			{
				$strSqlOrder = "ORDER BY QUANTITY";
			}
			if ($order!="asc")
			{
				$strSqlOrder .= " desc ";
			}
			$strSql = "
				SELECT
					count(PH.ID) as COUNTER
				FROM
					b_stat_phrase_list PH,
					b_stat_searcher S
				WHERE
				".$strSqlSearch."
				and S.ID = PH.SEARCHER_ID
				".$strSqlGroup."
			";
			$c = $DB->Query($strSql, false, $err_mess.__LINE__);
			$total = 0;
			$arrCount = array();
			while ($cr = $c->Fetch())
			{
				$total += intval($cr["COUNTER"]);
				$arrCount[] = intval($cr["COUNTER"]);
			}
			$max = (is_array($arrCount) && count($arrCount)>0) ? max($arrCount) : 0;
			if ($grby=="P")
			{
				$strSql = "
					SELECT /*TOP*/
						$s,
						count(PH.ID) QUANTITY,
						(count(PH.ID)*100)/$total C_PERCENT
					FROM
						b_stat_phrase_list PH,
						b_stat_searcher S
					WHERE
					".$strSqlSearch."
					and S.ID = PH.SEARCHER_ID
					".$strSqlGroup."
					".$strSqlOrder."
				";
			}
			else
			{
				$strSql = "
					SELECT /*TOP*/
						$s,
						count(PH.ID) QUANTITY,
						(count(PH.ID)*100)/$total C_PERCENT,
						S.PHRASES_HITS/S.PHRASES AVERAGE_HITS
					FROM
						b_stat_phrase_list PH,
						b_stat_searcher S
					WHERE
					".$strSqlSearch."
					and S.ID = PH.SEARCHER_ID
					".$strSqlGroup."
					".$strSqlOrder."
				";
			}
		}
		else//if ($grby=="S")
		{
			if ($by == "s_name")				$strSqlOrder = "ORDER BY S.ID";
			elseif ($by == "s_quantity")		$strSqlOrder = "ORDER BY QUANTITY";
			elseif ($by == "s_average_hits")	$strSqlOrder = "ORDER BY AVERAGE_HITS";
			else
			{
				$strSqlOrder = "ORDER BY QUANTITY";
			}
			if ($order!="asc")
			{
				$strSqlOrder .= " desc ";
			}
			$strSql = "SELECT sum(S.PHRASES) TOTAL, max(S.PHRASES) MAX FROM b_stat_searcher S";
			$c = $DB->Query($strSql, false, $err_mess.__LINE__);
			$cr = $c->Fetch();
			$total = intval($cr["TOTAL"]);
			$max = intval($cr["MAX"]);
			$strSql = "
				SELECT /*TOP*/
					S.ID SEARCHER_ID,
					S.NAME SEARCHER_NAME,
					S.PHRASES QUANTITY,
					S.PHRASES*100/$total C_PERCENT,
					S.PHRASES_HITS/S.PHRASES AVERAGE_HITS
				FROM
					b_stat_searcher S
				WHERE
					".$DB->IsNull("S.PHRASES","0")." > 0
				".$strSqlOrder."
			";
		}

		$res = $DB->Query(CStatistics::DBTopSql($strSql), false, $err_mess.__LINE__);

		return $res;
	}
}
