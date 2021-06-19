<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/classes/general/statevent.php");

class CStatEvent extends CAllStatEvent
{
	public static function GetListByGuest($GUEST_ID, $EVENT_ID=false, $EVENT3=false, $SEC=false)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');

		$strSqlSearch = "";
		if ($EVENT_ID!==false)
			$strSqlSearch .= " and E.EVENT_ID='".intval($EVENT_ID)."' ";
		if ($EVENT3!==false)
			$strSqlSearch .= " and E.EVENT3='".$DB->ForSql($EVENT3,255)."' ";
		if ($SEC!==false)
			$strSqlSearch .= " and E.DATE_ENTER > DATE_ADD(now(),INTERVAL - ".intval($SEC)." SECOND) ";

		$strSql = "
			SELECT
				E.ID
			FROM
				b_stat_event_list E
			WHERE
				E.GUEST_ID = ".intval($GUEST_ID)."
				".$strSqlSearch."
		";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		return $res;
	}

	public static function Add($EVENT_ID, $EVENT3, $DATE_ENTER, $PARAM, $MONEY="", $CURRENCY="", $CHARGEBACK="N")
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');

		$EVENT_ID = intval($EVENT_ID);
		$EVENT_LIST_ID = 0;
		$strSql = "SELECT KEEP_DAYS FROM b_stat_event WHERE ID = $EVENT_ID";
		$rsEvent = $DB->Query($strSql, false, $err_mess.__LINE__);
		if ($arEvent = $rsEvent->Fetch())
		{
			$MONEY = doubleval($MONEY);

			// если указана валюта то конвертируем
			if (trim($CURRENCY) <> '')
			{
				$base_currency = GetStatisticBaseCurrency();
				if ($base_currency <> '')
				{
					if ($CURRENCY!=$base_currency)
					{
						if (CModule::IncludeModule("currency"))
						{
							$rate = CCurrencyRates::GetConvertFactor($CURRENCY, $base_currency);
							if ($rate>0 && $rate!=1) $MONEY = $MONEY * $rate;
						}
					}
				}
			}
			$MONEY = round($MONEY,2);

			$arr = CStatEvent::DecodeGID($PARAM);
			$SESSION_ID		= intval($arr["SESSION_ID"]);
			$GUEST_ID		= intval($arr["GUEST_ID"]);
			$COUNTRY_ID		= $arr["COUNTRY_ID"];
			$ADV_ID			= intval($arr["ADV_ID"]);
			$ADV_BACK		= ($arr["ADV_BACK"]=="Y") ? "Y" : "N";
			$CHARGEBACK		= ($CHARGEBACK=="Y") ? "Y" : "N";
			$SITE_ID		= $arr["SITE_ID"];

			$DATE_ENTER = trim($DATE_ENTER) <> '' ? $DATE_ENTER : GetTime(time(),"FULL");
			$TIME_ENTER_TMSTMP = MakeTimeStamp($DATE_ENTER);
			if (!$TIME_ENTER_TMSTMP)
			{
				$DATE_ENTER = GetTime(time(),"FULL");
				$TIME_ENTER_TMSTMP = MakeTimeStamp($DATE_ENTER);
			}
			$TIME_ENTER_SQL = "FROM_UNIXTIME('".$TIME_ENTER_TMSTMP."')";
			$DAY_ENTER_TMSTMP = MakeTimeStamp($DATE_ENTER);
			$DAY_ENTER_SQL = "DATE(FROM_UNIXTIME('".$DAY_ENTER_TMSTMP."'))";

			$DB->StartTransaction();

			$arFields = array(
				"EVENT_ID"		=> $EVENT_ID,
				"EVENT3"		=> "'".$DB->ForSql($EVENT3,255)."'",
				"MONEY"			=> $MONEY,
				"DATE_ENTER"	=> $TIME_ENTER_SQL,
				"SESSION_ID"	=> (intval($SESSION_ID)>0) ? intval($SESSION_ID) : "null",
				"GUEST_ID"		=> (intval($GUEST_ID)>0) ? intval($GUEST_ID) : "null",
				"ADV_ID"		=> (intval($ADV_ID)>0) ? intval($ADV_ID) : "null",
				"ADV_BACK"		=> ($ADV_BACK=="Y") ? "'Y'" : "'N'",
				"COUNTRY_ID"	=> ($COUNTRY_ID <> '') ? "'".$DB->ForSql($COUNTRY_ID,2)."'" : "null",
				"KEEP_DAYS"		=> (intval($arEvent["KEEP_DAYS"])>0) ? intval($arEvent["KEEP_DAYS"]) : "null",
				"CHARGEBACK"	=> "'".$CHARGEBACK."'",
				"SITE_ID"		=> ($SITE_ID <> '') ? "'".$DB->ForSql($SITE_ID,2)."'" : "null"
				);
			$EVENT_LIST_ID = $DB->Insert("b_stat_event_list",$arFields, $err_mess.__LINE__);

			// увеличиваем счетчик для страны
			if ($COUNTRY_ID <> '')
				CStatistics::UpdateCountry($COUNTRY_ID, Array("C_EVENTS" => 1));

			// если нужно обновляем дату первого события для данного типа события
			$arFields = Array("DATE_ENTER" => $DB->GetNowFunction());
			$DB->Update("b_stat_event",$arFields,"WHERE ID='".$EVENT_ID."' and DATE_ENTER is null",$err_mess.__LINE__);
			// обновляем счетчик по дням для данного типа события
			$arFields = Array(
					"DATE_LAST"	=> $DB->GetNowFunction(),
					"COUNTER"	=> "COUNTER + 1",
					"MONEY"		=> "MONEY + ".$MONEY
					);
			$rows = $DB->Update("b_stat_event_day",$arFields,"WHERE EVENT_ID='".$EVENT_ID."' and DATE_STAT = ".$DAY_ENTER_SQL, $err_mess.__LINE__);
			// если обсчета по дням нет то
			if (intval($rows)<=0)
			{
				// добавляем его
				$arFields_i = Array(
					"DATE_STAT"	=> $DAY_ENTER_SQL,
					"DATE_LAST"	=> $TIME_ENTER_SQL,
					"EVENT_ID"	=> $EVENT_ID,
					"COUNTER"	=> 1,
					"MONEY"		=> $MONEY
					);
				$DB->Insert("b_stat_event_day",$arFields_i, $err_mess.__LINE__);
			}
			elseif (intval($rows)>1) // если обновили более одного дня то
			{
				// удалим лишние
				$strSql = "SELECT ID FROM b_stat_event_day WHERE EVENT_ID='".$EVENT_ID."' and DATE_STAT = ".$DAY_ENTER_SQL." ORDER BY ID";
				$i=0;
				$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
				while ($ar = $rs->Fetch())
				{
					$i++;
					if ($i>1)
					{
						$strSql = "DELETE FROM b_stat_event_day WHERE ID = ".$ar["ID"];
						$DB->Query($strSql, false, $err_mess.__LINE__);
					}
				}
			}

			// обновляем сессию и гостя
			$arFields = Array("C_EVENTS" => "C_EVENTS+1");
			$DB->Update("b_stat_session",$arFields,"WHERE ID=".$SESSION_ID, $err_mess.__LINE__,false,false,false);
			$DB->Update("b_stat_guest",$arFields,"WHERE ID=".$GUEST_ID, $err_mess.__LINE__,false,false,false);

			// обновляем дневной счетчик
			$arFields = Array("C_EVENTS" => "C_EVENTS + 1");
			$DB->Update("b_stat_day",$arFields,"WHERE DATE_STAT = ".$DAY_ENTER_SQL, $err_mess.__LINE__,false,false,false);

			// увеличиваем счетчик траффика
			CTraffic::IncParam(array("EVENT" => 1), array(), false, $DATE_ENTER);

			// если сайт определен то
			if ($SITE_ID <> '')
			{
				// обновляем дневной счетчик
				$arFields = Array("C_EVENTS" => "C_EVENTS+1");
				$DB->Update("b_stat_day_site", $arFields, "WHERE SITE_ID='".$DB->ForSql($SITE_ID,2)."' and DATE_STAT = ".$DAY_ENTER_SQL, $err_mess.__LINE__);

				// увеличиваем счетчик траффика
				CTraffic::IncParam(array(), array("EVENT" => 1), $SITE_ID, $DATE_ENTER);
			}

			if ($ADV_ID>0)
			{
				$a = $DB->Query("SELECT 'x' FROM b_stat_adv WHERE ID='".$ADV_ID."'", false, $err_mess.__LINE__);
				// если есть такая рекламная кампания то
				if ($ar = $a->Fetch())
				{
					// увеличиваем доход рекламной кампании
					if ($MONEY!=0)
					{
						$sign = ($CHARGEBACK=="Y") ? "-" : "+";
						$arFields = array("REVENUE" => "REVENUE ".$sign." ".$MONEY);
						$DB->Update("b_stat_adv",$arFields,"WHERE ID='$ADV_ID'",$err_mess.__LINE__,false,false,false);
					}
					// обновляем счетчик связки рекламной кампании и типа события
					if ($ADV_BACK=="Y")
					{
						$arFields = array(
							"COUNTER_BACK"	=> "COUNTER_BACK + 1",
							"MONEY_BACK"	=> "MONEY_BACK + ".$MONEY
							);
					}
					else
					{
						$arFields = array(
							"COUNTER"	=> "COUNTER + 1",
							"MONEY"		=> "MONEY + ".$MONEY
							);
					}
					$rows = $DB->Update("b_stat_adv_event",$arFields,"WHERE ADV_ID='$ADV_ID' and EVENT_ID='$EVENT_ID'",$err_mess.__LINE__);
					// если связки нет то
					if (intval($rows)<=0 && intval($ADV_ID)>0 && intval($EVENT_ID)>0)
					{
						// вставляем связку
						$arFields = Array(
							"ADV_ID"	=> "'".intval($ADV_ID)."'",
							"EVENT_ID"	=> "'".intval($EVENT_ID)."'"
							);
						if ($ADV_BACK=="Y")
						{
							$arFields["COUNTER_BACK"] = 1;
							$arFields["MONEY_BACK"] = $MONEY;
						}
						else
						{
							$arFields["COUNTER"] = 1;
							$arFields["MONEY"] = $MONEY;
						}
						$DB->Insert("b_stat_adv_event", $arFields, $err_mess.__LINE__);
					}

					// обновляем счетчик связки по дням
					if ($ADV_BACK=="Y")
					{
						$arFields = array(
							"COUNTER_BACK"	=> "COUNTER_BACK + 1",
							"MONEY_BACK"	=> "MONEY_BACK + ".$MONEY
							);
					}
					else
					{
						$arFields = array(
							"COUNTER"	=> "COUNTER + 1",
							"MONEY"		=> "MONEY + ".$MONEY
							);
					}
					$rows = $DB->Update("b_stat_adv_event_day",$arFields,"WHERE ADV_ID='$ADV_ID' and EVENT_ID='$EVENT_ID' and DATE_STAT = ".$DAY_ENTER_SQL, $err_mess.__LINE__,false,false,false);
					// если нет такой связки то
					if (intval($rows)<=0 && intval($ADV_ID)>0 && intval($EVENT_ID)>0)
					{
						// вставляем ее
						$arFields = Array(
							"DATE_STAT"	=> $DAY_ENTER_SQL,
							"ADV_ID"	=> "'".$ADV_ID."'",
							"EVENT_ID"	=> "'".$EVENT_ID."'"
							);
						if ($ADV_BACK=="Y")
						{
							$arFields["COUNTER_BACK"] = 1;
							$arFields["MONEY_BACK"] = $MONEY;
						}
						else
						{
							$arFields["COUNTER"] = 1;
							$arFields["MONEY"] = $MONEY;
						}
						$DB->Insert("b_stat_adv_event_day", $arFields, $err_mess.__LINE__);
					}
				}
			}
			$DB->Commit();
		}
		return intval($EVENT_LIST_ID);
	}

	public static function GetList($by = 's_id', $order = 'desc', $arFilter = [])
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$arSqlSearch = Array();
		$arSqlSearch_h = Array();
		$strSqlSearch_h = "";
		$CURRENCY = "";
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
					case "EVENT_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("E.".$key,$val,$match);
						break;
					case "EVENT_NAME":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("V.NAME",$val, $match);
						break;
					case "EVENT1":
					case "EVENT2":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("V.".$key,$val, $match);
						break;
					case "EVENT3":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.EVENT3",$val, $match);
						break;
					case "DATE":
						if (CheckDateTime($val))
							$arSqlSearch[] = "E.DATE_ENTER=".$DB->CharToDateFunction($val);
						break;
					case "DATE1":
						if (CheckDateTime($val))
							$arSqlSearch[] = "E.DATE_ENTER>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE2":
						if (CheckDateTime($val))
							$arSqlSearch[] = "E.DATE_ENTER<".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
						break;
					case "REDIRECT_URL":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.REDIRECT_URL",$val,$match,array("/","\\",".","?","#",":"));
						break;
					case "MONEY":
						$arSqlSearch_h[] = "MONEY='".roundDB($val)."'";
						break;
					case "MONEY1":
						$arSqlSearch_h[] = "MONEY>='".roundDB($val)."'";
						break;
					case "MONEY2":
						$arSqlSearch_h[] = "MONEY<='".roundDB($val)."'";
						break;
					case "SESSION_ID":
					case "GUEST_ID":
					case "ADV_ID":
					case "HIT_ID":
					case "COUNTRY_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("E.".$key,$val,$match);
						break;
					case "ADV_BACK":
						$arSqlSearch[] = ($val=="Y") ? "E.ADV_BACK='Y'" : "E.ADV_BACK='N'";
						break;
					case "REFERER_URL":
					case "URL":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.".$key,$val,$match,array("/","\\",".","?","#",":"));
						break;
					case "COUNTRY":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("C.NAME", $val, $match);
						break;
					case "SITE_ID":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("E.SITE_ID", $val, $match);
						break;
					case "REFERER_SITE_ID":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("E.REFERER_SITE_ID", $val, $match);
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
		if ($base_currency <> '')
		{
			if (CModule::IncludeModule("currency"))
			{
				if ($CURRENCY!=$base_currency && $CURRENCY <> '')
				{
					$rate = CCurrencyRates::GetConvertFactor($base_currency, $CURRENCY);
					$view_currency = $CURRENCY;
				}
			}
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		foreach($arSqlSearch_h as $sqlWhere)
			$strSqlSearch_h .= " and (".$sqlWhere.") ";

		if ($by == "s_id")					$strSqlOrder = "ORDER BY E.ID";
		elseif ($by == "s_site_id")			$strSqlOrder = "ORDER BY E.SITE_ID";
		elseif ($by == "s_event_id" || $by == "s_type_id")		$strSqlOrder = "ORDER BY E.EVENT_ID";
		elseif ($by == "s_event3")			$strSqlOrder = "ORDER BY E.EVENT3";
		elseif ($by == "s_date_enter")		$strSqlOrder = "ORDER BY E.DATE_ENTER";
		elseif ($by == "s_adv_id")			$strSqlOrder = "ORDER BY E.ADV_ID";
		elseif ($by == "s_adv_back")		$strSqlOrder = "ORDER BY E.ADV_BACK";
		elseif ($by == "s_session_id")		$strSqlOrder = "ORDER BY E.SESSION_ID";
		elseif ($by == "s_guest_id")		$strSqlOrder = "ORDER BY E.GUEST_ID";
		elseif ($by == "s_hit_id")			$strSqlOrder = "ORDER BY E.HIT_ID";
		elseif ($by == "s_url")				$strSqlOrder = "ORDER BY E.URL";
		elseif ($by == "s_referer_url")		$strSqlOrder = "ORDER BY E.REFERER_URL";
		elseif ($by == "s_redirect_url")	$strSqlOrder = "ORDER BY E.REDIRECT_URL";
		elseif ($by == "s_country_id")		$strSqlOrder = "ORDER BY E.COUNTRY_ID";
		elseif ($by == "s_money")			$strSqlOrder = "ORDER BY MONEY";
		else
		{
			$strSqlOrder = "ORDER BY E.ID";
		}

		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
		}

		if($arFilter["GROUP"]=="total")
		{
			$strSql =	"
				SELECT
					COUNT(1)							COUNTER,
					round(sum(if(E.CHARGEBACK='Y',-E.MONEY,E.MONEY)*$rate),2)	MONEY,
					'".$DB->ForSql($view_currency)."'						CURRENCY
				FROM
					b_stat_event_list E
				INNER JOIN b_stat_event V ON (V.ID=E.EVENT_ID)
				LEFT JOIN b_stat_country C ON (C.ID=E.COUNTRY_ID)
				WHERE
				$strSqlSearch
				HAVING
					1=1
					$strSqlSearch_h
				";
		}
		else
		{
			$strSql =	"
				SELECT
					E.ID, E.EVENT3, E.EVENT_ID, E.ADV_ID, E.ADV_BACK, E.COUNTRY_ID, E.SESSION_ID, E.GUEST_ID, E.HIT_ID, E.REFERER_URL, E.URL, E.REDIRECT_URL, E.CHARGEBACK, E.SITE_ID, E.REFERER_SITE_ID,
					round((E.MONEY*$rate),2)										MONEY,
					'".$DB->ForSql($view_currency)."'												CURRENCY,
					".$DB->DateToCharFunction("E.DATE_ENTER")."						DATE_ENTER,
					V.ID															TYPE_ID,
					V.DESCRIPTION, V.NAME, V.EVENT1, V.EVENT2,
					C.NAME															COUNTRY_NAME,
					if (length(V.NAME)>0, V.NAME,
						concat(ifnull(V.EVENT1,''),' / ',ifnull(V.EVENT2,'')))		EVENT
				FROM
					b_stat_event_list E
				INNER JOIN b_stat_event V ON (V.ID=E.EVENT_ID)
				LEFT JOIN b_stat_country C ON (C.ID=E.COUNTRY_ID)
				WHERE
				$strSqlSearch
				HAVING
					1=1
					$strSqlSearch_h
				$strSqlOrder
				LIMIT ".intval(COption::GetOptionString('statistic','RECORDS_LIMIT'))."
				";
		}

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		return $res;
	}

	public static function Delete($ID)
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$ID = intval($ID);
		$strSql = "
			SELECT
				L.EVENT_ID,
				L.MONEY,
				L.SESSION_ID,
				L.GUEST_ID,
				L.ADV_ID,
				L.ADV_BACK,
				L.COUNTRY_ID,
				L.CHARGEBACK,
				L.SITE_ID,
				".$DB->DateToCharFunction("L.DATE_ENTER","SHORT")."	DATE_ENTER,
				".$DB->DateToCharFunction("L.DATE_ENTER","FULL")."	DATE_ENTER_FULL
			FROM
				b_stat_event_list L,
				b_stat_event E
			WHERE
				L.ID = '$ID'
			and E.ID = L.EVENT_ID
			";
		$a = $DB->Query($strSql, false, $err_mess.__LINE__);
		if ($ar = $a->Fetch())
		{
			// уменьшаем счетчик у страны
			CStatistics::UpdateCountry($ar["COUNTRY_ID"], Array("C_EVENTS" => 1), $ar["DATE_ENTER"], "SHORT", "-");

			// уменьшаем счетчик по дням
			$arFields = Array(
				"COUNTER"	=> "COUNTER-1",
				"MONEY"		=> "MONEY - ".doubleval($ar["MONEY"])
				);
			$rows = $DB->Update("b_stat_event_day",$arFields,"WHERE EVENT_ID='".intval($ar["EVENT_ID"])."' and DATE_STAT = FROM_UNIXTIME('".MkDateTime(ConvertDateTime($ar["DATE_ENTER"],"D.M.Y"),"d.m.Y")."')",$err_mess.__LINE__);
			// если уже была свертка то
			if (intval($rows)<=0)
			{
				// уменьшим счетчик на типе события
				$arFields = Array(
					"COUNTER"	=> "COUNTER-1",
					"MONEY"		=> "MONEY - ".doubleval($ar["MONEY"])
					);
				$DB->Update("b_stat_event",$arFields,"WHERE ID='".intval($ar["EVENT_ID"])."'",$err_mess.__LINE__);
			}
			// если в связке есть нулевые значения то ее можно удалить
			$strSql = "DELETE FROM b_stat_event_day WHERE COUNTER=0";
			$DB->Query($strSql,false,$err_mess.__LINE__);

			// чистим сессию
			$arFields = Array("C_EVENTS" => "C_EVENTS-1");
			$DB->Update("b_stat_session",$arFields,"WHERE ID='".intval($ar["SESSION_ID"])."'",$err_mess.__LINE__,false,false,false);

			// чистим гостя
			$DB->Update("b_stat_guest",$arFields,"WHERE ID='".intval($ar["GUEST_ID"])."'",$err_mess.__LINE__,false,false,false);

			if (intval($ar["ADV_ID"])>0)
			{
				// изменяем доход рекламной кампании
				if (doubleval($ar["MONEY"])!=0)
				{
					$sign = ($ar["CHARGEBACK"]=="Y") ? "+" : "-";
					$arFields = array("REVENUE" => "REVENUE ".$sign." ".doubleval($ar["MONEY"]));
					$DB->Update("b_stat_adv",$arFields,"WHERE ID='".intval($ar["ADV_ID"])."'", $err_mess.__LINE__,false,false,false);
				}

				// чистим связку с рекламной кампанией
				if ($ar["ADV_BACK"]=="Y")
				{
					$arFields = array(
						"COUNTER_BACK"	=> "COUNTER_BACK - 1",
						"MONEY_BACK"	=> "MONEY_BACK - ".doubleval($ar["MONEY"]),
						);
				}
				else
				{
					$arFields = array(
						"COUNTER"	=> "COUNTER - 1",
						"MONEY"		=> "MONEY - ".doubleval($ar["MONEY"]),
						);
				}
				$DB->Update("b_stat_adv_event",$arFields,"WHERE ADV_ID='".intval($ar["ADV_ID"])."' and EVENT_ID='".$ar["EVENT_ID"]."'",$err_mess.__LINE__);

				// чистим связку с рекламной кампанией по дням
				if ($ar["ADV_BACK"]=="Y")
				{
					$arFields = array(
						"COUNTER_BACK"	=> "COUNTER_BACK - 1",
						"MONEY_BACK"	=> "MONEY_BACK - ".doubleval($ar["MONEY"]),
						);
				}
				else
				{
					$arFields = array(
						"COUNTER"	=> "COUNTER - 1",
						"MONEY"		=> "MONEY - ".doubleval($ar["MONEY"]),
						);
				}
				$DB->Update("b_stat_adv_event_day",$arFields,"WHERE ADV_ID='".intval($ar["ADV_ID"])."' and EVENT_ID='".$ar["EVENT_ID"]."' and DATE_STAT = FROM_UNIXTIME('".MkDateTime(ConvertDateTime($ar["DATE_ENTER"],"D.M.Y"),"d.m.Y")."')",$err_mess.__LINE__,false,false,false);
			}
			// если в связках остались нулевые значения то их можно удалить
			$strSql = "DELETE FROM b_stat_adv_event WHERE COUNTER<=0 and COUNTER_BACK<=0";
			$DB->Query($strSql, false, $err_mess.__LINE__);
			$strSql = "DELETE FROM b_stat_adv_event_day WHERE COUNTER<=0 and COUNTER_BACK<=0";
			$DB->Query($strSql, false, $err_mess.__LINE__);

			// уменьшаем счетчик по дням
			$arFields = Array("C_EVENTS" => "C_EVENTS-1");
			$DB->Update("b_stat_day",$arFields,"WHERE DATE_STAT = FROM_UNIXTIME('".MkDateTime(ConvertDateTime($ar["DATE_ENTER"],"D.M.Y"),"d.m.Y")."')", $err_mess.__LINE__);

			// уменьшаем счетчик траффика
			CTraffic::DecParam(array("EVENT" => 1), array(), false, $ar["DATE_ENTER_FULL"]);

			if ($ar["SITE_ID"] <> '')
			{
				$arFields = Array("C_EVENTS" => "C_EVENTS-1");
				$DB->Update("b_stat_day_site",$arFields,"WHERE SITE_ID = '".$DB->ForSql($ar["SITE_ID"], 2)."' and  DATE_STAT = FROM_UNIXTIME('".MkDateTime(ConvertDateTime($ar["DATE_ENTER"],"D.M.Y"),"d.m.Y")."')", $err_mess.__LINE__);

				// уменьшаем счетчик траффика
				CTraffic::DecParam(array(), array("EVENT" => 1), $ar["SITE_ID"], $ar["DATE_ENTER_FULL"]);
			}

			$strSql = "DELETE FROM b_stat_event_list WHERE ID='$ID'";
			$DB->Query($strSql, false, $err_mess.__LINE__);

			return true;
		}
		return false;
	}
}
