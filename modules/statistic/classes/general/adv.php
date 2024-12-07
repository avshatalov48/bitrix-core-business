<?php

class CAllAdv
{
	public static function SetByReferer($referer1, $referer2, &$arrADV, &$ref1, &$ref2)
	{
		$DB = CDatabase::GetModuleConnection('statistic');

		// lookup campaign with referer1 and referer2
		$referer1 = trim($referer1);
		$referer1_sql = $referer1 <> ''? "REFERER1='".$DB->ForSql($referer1, 255)."'": "(REFERER1 is null or ".$DB->Length("REFERER1")."=0)";
		$referer2 = trim($referer2);
		$referer2_sql = $referer2 <> ''? "REFERER2='".$DB->ForSql($referer2, 255)."'": "(REFERER2 is null or ".$DB->Length("REFERER2")."=0)";

		$strSql = "
			SELECT
				ID,
				REFERER1,
				REFERER2
			FROM
				b_stat_adv
			WHERE
				".$referer1_sql."
				and ".$referer2_sql."
		";
		$w = $DB->Query($strSql);

		$found = false;
		while ($wr = $w->Fetch())
		{
			$found = true;
			// return with parameters
			$arrADV[] = intval($wr["ID"]);
			$ref1 = $wr["REFERER1"];
			$ref2 = $wr["REFERER2"];
		}

		if(!$found)
		{
			$NA = "";
			if(COption::GetOptionString("statistic", "ADV_NA") == "Y")
			{
				$NA_1 = COption::GetOptionString("statistic", "AVD_NA_REFERER1");
				$NA_2 = COption::GetOptionString("statistic", "AVD_NA_REFERER2");
				if (($NA_1 <> '' || $NA_2 <> '') && $referer1==$NA_1 && $referer2==$NA_2)
					$NA = "Y";
			}

			if((COption::GetOptionString("statistic", "ADV_AUTO_CREATE") == "Y") || ($NA == "Y"))
			{
				if(COption::GetOptionString("statistic", "REFERER_CHECK") == "Y")
				{
					$bGoodR = preg_match("/^([0-9A-Za-z_:;.,-])*$/", $referer1);
					if($bGoodR)
						$bGoodR = preg_match("/^([0-9A-Za-z_:;.,-])*$/", $referer2);
				}
				else
				{
					$bGoodR = true;
				}

				if($bGoodR)
				{
					// add new advertising campaign
					$arFields = Array(
						"REFERER1" => $referer1 <> '' ? "'".$DB->ForSql($referer1, 255)."'" : "null",
						"REFERER2" => $referer2 <> '' ? "'".$DB->ForSql($referer2, 255)."'" : "null",
						"DATE_FIRST" => $DB->GetNowFunction(),
						"DATE_LAST" => $DB->GetNowFunction(),
					);
					$arrADV[] = $DB->Insert("b_stat_adv", $arFields);
					$ref1 = $referer1;
					$ref2 = $referer2;
				}
			}
		}
	}

	public static function SetByPage($page, &$arrADV, &$ref1, &$ref2, $type="TO")
	{
		$DB = CDatabase::GetModuleConnection('statistic');

		$strSql = "
			SELECT
				A.ID,
				A.REFERER1,
				A.REFERER2
			FROM
				b_stat_adv A
			INNER JOIN b_stat_adv_page AP ON (AP.ADV_ID = A.ID and AP.C_TYPE='".$DB->ForSQL($type)."')
			WHERE
				AP.PAGE is not null
				and ".$DB->Length("AP.PAGE")." > 0
				and '".$DB->ForSQL($page)."' like ".$DB->Concat("'%'", "AP.PAGE", "'%'")."
			";

		$w = $DB->Query($strSql);
		while ($wr=$w->Fetch())
		{
			$arrADV[] = intval($wr["ID"]);
			$ref1 = $wr["REFERER1"];
			$ref2 = $wr["REFERER2"];
		}
	}

	// returns arrays for graphics plot
	public static function GetAnalysisGraphArray($arFilter, &$is_filtered, $DATA_TYPE="SESSION_SUMMA", &$arrLegend, &$summa, &$max)
	{
		$DB = CDatabase::GetModuleConnection('statistic');

		$arSqlSearch = Array();
		switch ($DATA_TYPE)
		{
			case "SESSION_SUMMA":
			case "SESSION":
			case "SESSION_BACK":
			case "VISITOR_SUMMA":
			case "VISITOR":
			case "VISITOR_BACK":
			case "NEW_VISITOR":
			case "FAV_SUMMA":
			case "FAV":
			case "FAV_BACK":
			case "HOST_SUMMA":
			case "HOST":
			case "HOST_BACK":
			case "HIT_SUMMA":
			case "HIT":
			case "HIT_BACK":
				unset($arFilter["EVENT_TYPE_ID"]);
				unset($arFilter["EVENT_TYPE"]);
				unset($arFilter["EVENT1"]);
				unset($arFilter["EVENT2"]);
				break;
		}
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
					case "DATE1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "D.DATE_STAT>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "D.DATE_STAT<=".$DB->CharToDateFunction($val." 23:59:59", "FULL");
						break;
					case "EVENT_TYPE_ID":
					case "EVENT_TYPE":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("D.EVENT_ID",$val,$match);
						break;
					case "ADV_ID":
					case "ADV":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("D.ADV_ID",$val,$match);
						break;
					case "REFERER1":
					case "REFERER2":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("A.".$key, $val, $match);
						break;
					case "EVENT1":
					case "EVENT2":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.".$key, $val, $match);
						break;
				}
			}
		}
		$arrDays = array();
		$arrLegend = array();
		$arrSum = array();
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = CAdv::GetAnalysisGraphArray_SQL($strSqlSearch, $DATA_TYPE);

		$rsD = $DB->Query($strSql);
		while ($arD = $rsD->Fetch())
		{
			switch($DATA_TYPE)
			{
				default:				$cnt = intval($arD["SESSIONS"])+intval($arD["SESSIONS_BACK"]);			break;
				case "SESSION":			$cnt = intval($arD["SESSIONS"]);										break;
				case "SESSION_BACK":	$cnt = intval($arD["SESSIONS_BACK"]);									break;
				case "VISITOR_SUMMA":	$cnt = intval($arD["GUESTS"])+intval($arD["GUESTS_BACK"]);				break;
				case "VISITOR":			$cnt = intval($arD["GUESTS"]);											break;
				case "VISITOR_BACK":	$cnt = intval($arD["GUESTS_BACK"]);										break;
				case "NEW_VISITOR":		$cnt = intval($arD["NEW_GUESTS"]);										break;
				case "FAV_SUMMA":		$cnt = intval($arD["FAVORITES"])+intval($arD["FAVORITES_BACK"]);		break;
				case "FAV":				$cnt = intval($arD["FAVORITES"]);										break;
				case "FAV_BACK":		$cnt = intval($arD["FAVORITES_BACK"]);									break;
				case "HOST_SUMMA":		$cnt = intval($arD["C_HOSTS"])+intval($arD["HOSTS_BACK"]);				break;
				case "HOST":			$cnt = intval($arD["C_HOSTS"]);											break;
				case "HOST_BACK":		$cnt = intval($arD["HOSTS_BACK"]);										break;
				case "HIT_SUMMA":		$cnt = intval($arD["HITS"])+intval($arD["HITS_BACK"]);					break;
				case "HIT":				$cnt = intval($arD["HITS"]);											break;
				case "HIT_BACK":		$cnt = intval($arD["HITS_BACK"]);										break;
				case "EVENT_SUMMA":		$cnt = intval($arD["EVENTS"])+intval($arD["EVENTS_BACK"]);				break;
				case "EVENT":			$cnt = intval($arD["EVENTS"]);											break;
				case "EVENT_BACK":		$cnt = intval($arD["EVENTS_BACK"]);										break;
				case "MONEY_SUMMA":		$cnt = doubleval($arD["MONEY"])+doubleval($arD["MONEY_BACK"]);			break;
				case "MONEY":			$cnt = doubleval($arD["MONEY"]);										break;
				case "MONEY_BACK":		$cnt = doubleval($arD["MONEY_BACK"]);									break;
			}
			if ($cnt>0)
			{
				$arrDays[$arD["DATE_STAT"]]["D"] = $arD["DAY"];
				$arrDays[$arD["DATE_STAT"]]["M"] = $arD["MONTH"];
				$arrDays[$arD["DATE_STAT"]]["Y"] = $arD["YEAR"];
				$arrDays[$arD["DATE_STAT"]][$arD["ADV_ID"]] = $cnt;
				$arrLegend[$arD["ADV_ID"]]["ID"] = $arD["ADV_ID"];
				$arrLegend[$arD["ADV_ID"]]["R1"] = $arD["REFERER1"];
				$arrLegend[$arD["ADV_ID"]]["R2"] = $arD["REFERER2"];
				$arrSum[$arD["ADV_ID"]] += $cnt;
			}
		}

		$color = "";
		$summa = 0;
		$max = 0;
		$total = sizeof($arrLegend);
		foreach ($arrLegend as $key => $arr)
		{
			$color = GetNextRGB($color, $total);
			$arr["CLR"] = $color;
			$arrLegend[$key] = $arr;
			$arrLegend[$key]["SM"] = $arrSum[$key];
			$summa += $arrSum[$key];
			if ($arrSum[$key]>$max) $max = $arrSum[$key];
		}

		$is_filtered = (IsFiltered($strSqlSearch));
		return $arrDays;
	}

	public static function Delete($ID)
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$ID = intval($ID);
		if ($ID>0)
		{
			CAdv::Reset($ID);
			$strSql = "DELETE FROM b_stat_adv_page WHERE ADV_ID=$ID";
			$DB->Query($strSql);
			$strSql = "DELETE FROM b_stat_adv WHERE ID=$ID";
			$DB->Query($strSql);
			return true;
		}
		return false;
	}

	public static function Reset($ID)
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$ID = intval($ID);
		if ($ID>0)
		{
			$DB->StartTransaction();
			$strSql = "DELETE FROM b_stat_adv_guest WHERE ADV_ID=$ID";
			$DB->Query($strSql);
			$strSql = "DELETE FROM b_stat_adv_event WHERE ADV_ID=$ID";
			$DB->Query($strSql);
			$strSql = "DELETE FROM b_stat_adv_searcher WHERE ADV_ID=$ID";
			$DB->Query($strSql);
			$strSql = "DELETE FROM b_stat_adv_day WHERE ADV_ID=$ID";
			$DB->Query($strSql);
			$strSql = "DELETE FROM b_stat_adv_event_day WHERE ADV_ID=$ID";
			$DB->Query($strSql);
			$strSql = "DELETE FROM b_stat_path_adv WHERE ADV_ID=$ID";
			$DB->Query($strSql);
			$arFields = array(
				"GUESTS" => 0,
				"NEW_GUESTS" => 0,
				"FAVORITES" => 0,
				"C_HOSTS" => 0,
				"SESSIONS" => 0,
				"HITS" => 0,
				"DATE_FIRST" => "null",
				"DATE_LAST" => "null",
				"GUESTS_BACK" => 0,
				"FAVORITES_BACK" => 0,
				"HOSTS_BACK" => 0,
				"SESSIONS_BACK" => 0,
				"HITS_BACK" => 0
			);
			$DB->Update("b_stat_adv",$arFields,"WHERE ID=$ID",'',false,false,false);
			$DB->Commit();
			return true;
		}
		return false;
	}

	public static function DynamicDays($ADV_ID, $date1="", $date2="")
	{
		$arFilter = array("DATE1"=>$date1, "DATE2"=>$date2);
		$d=0;
		$arMaxMin=array();
		$z = CAdv::GetDynamicList($ADV_ID, '', '', $arMaxMin, $arFilter);
		while ($zr=$z->Fetch()) $d++;
		return $d;
	}
}
