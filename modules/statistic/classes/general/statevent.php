<?php
class CAllStatEvent
{
	///////////////////////////////////////////////////////////////////
	// Returns string formatted as follows:
	// [sites group ID].<session ID>.<guest ID>.<country ID>.<adv compaign ID>.<adv compaign return Y|N>.<site ID>
	///////////////////////////////////////////////////////////////////
	public static function GetGID($site_id=false)
	{
		$s = "";

		$COUNTRY_ID = $_SESSION["SESS_COUNTRY_ID"];
		if ($_SESSION["SESS_COUNTRY_ID"] == '') $COUNTRY_ID = "N0";

		$s .= $_SESSION["SESS_SESSION_ID"].".".$_SESSION["SESS_GUEST_ID"].".".$COUNTRY_ID;

		if (intval($_SESSION["SESS_ADV_ID"])>0) $s .= ".".$_SESSION["SESS_ADV_ID"].".N";
		elseif (intval($_SESSION["SESS_LAST_ADV_ID"])>0) $s .= ".".$_SESSION["SESS_LAST_ADV_ID"].".Y";
		else $s .= "..";

		if ($site_id===false)
		{
			if (defined("ADMIN_SECTION") && ADMIN_SECTION===true) $site_id = "";
			elseif (defined("SITE_ID")) $site_id = SITE_ID;
		}
		if ($site_id <> '') $s .= ".".$site_id;
		else $s .= ".";

		$encode = COption::GetOptionString("statistic","EVENT_GID_BASE64_ENCODE");
		if ($encode=="Y") $s = base64_encode($s);

		return GetStatGroupSiteID().".".$s;
	}

	///////////////////////////////////////////////////////////////////
	// Event creation
	///////////////////////////////////////////////////////////////////
	public static function AddCurrent($event1, $event2="", $event3="", $money="", $currency="", $goto="", $chargeback="N", $site_id=false)
	{
		$DB = CDatabase::GetModuleConnection('statistic');

		$event1 = trim($event1);
		$event2 = trim($event2);
		$event3 = trim($event3);

		if($event1 == '' && $event2 == '')
			return array("EVENT_ID"=>0, "TYPE_ID"=>0, "EID"=>0);

		//Check if register event for searcher
		if(intval($_SESSION["SESS_SEARCHER_ID"]) > 0 && COption::GetOptionString("statistic", "SEARCHER_EVENTS")!="Y")
			return array("EVENT_ID"=>0, "TYPE_ID"=>0, "EID"=>0);

		// lookup event type ID
		$EVENT_ID = CStatEvent::SetEventType($event1, $event2, $arEventType);
		// return if it's unknown
		if($EVENT_ID <= 0)
			return array("EVENT_ID"=>0, "TYPE_ID"=>0, "EID"=>0);

		if ($site_id===false)
		{
			if (defined("ADMIN_SECTION") && ADMIN_SECTION===true)
			{
				$sql_site = "null";
				$site_id = false;
			}
			elseif (defined("SITE_ID"))
			{
				$sql_site = "'".$DB->ForSql(SITE_ID,2)."'";
				$site_id = SITE_ID;
			}
			else
			{
				$sql_site = "null";
				$site_id = false;
			}
		}
		else
		{
			if (trim($site_id) <> '')
			{
				$sql_site = "'".$DB->ForSql($site_id,2)."'";
			}
			else
			{
				$sql_site = "null";
				$site_id = false;
			}
		}

		$money = doubleval($money);
		// convert when currency specified
		if (trim($currency) <> '')
		{
			$base_currency = GetStatisticBaseCurrency();
			if ($base_currency <> '')
			{
				if ($currency!=$base_currency)
				{
					if (CModule::IncludeModule("currency"))
					{
						$rate = CCurrencyRates::GetConvertFactor($currency, $base_currency);
						if ($rate>0 && $rate!=1)
							$money = $money * $rate;
					}
				}
			}
		}
		$money = round($money,2);
		$chargeback = ($chargeback=="Y") ? "Y" : "N";

		$goto = preg_replace("/#EVENT_GID#/i", CStatEvent::GetGID($site_id), $goto);
		$sql_KEEP_DAYS = (intval($arEventType["KEEP_DAYS"])>0) ? intval($arEventType["KEEP_DAYS"]) : "null";

		$arr = false;
		$referer_url = $_SERVER["HTTP_REFERER"] == '' ? $_SESSION["SESS_HTTP_REFERER"] : $_SERVER["HTTP_REFERER"];
		if ($referer_url <> '')
		{
			if($url = @parse_url($referer_url))
			{
				$rs = CSite::GetList("LENDIR", "DESC", Array("ACTIVE"=>"Y", "DOMAIN"=>"%".$url["host"], "IN_DIR"=>$url["path"]));
				$arr = $rs->Fetch();
			}
		}
		$sql_referer_site_id = is_array($arr) && ($arr["ID"] <> '')? "'".$DB->ForSql($arr["ID"],2)."'": "null";
		$HIT_ID = CKeepStatistics::GetCuurentHitID();

		$arFields = Array(
			"EVENT_ID" => "'".$EVENT_ID."'",
			"EVENT3" => "'".$DB->ForSql($event3,255)."'",
			"MONEY" => $money,
			"DATE_ENTER" => $DB->GetNowFunction(),
			"REFERER_URL" => "'".$DB->ForSql($referer_url,2000)."'",
			"URL" => "'".$DB->ForSql(__GetFullRequestUri(),2000)."'",
			"REDIRECT_URL" => "'".$DB->ForSql($goto,2000)."'",
			"SESSION_ID" => (intval($_SESSION["SESS_SESSION_ID"])>0) ? intval($_SESSION["SESS_SESSION_ID"]) : "null",
			"GUEST_ID" => (intval($_SESSION["SESS_GUEST_ID"])>0) ? intval($_SESSION["SESS_GUEST_ID"]) : "null",
			"ADV_ID" => ($_SESSION["SESS_LAST_ADV_ID"]>0) ? $_SESSION["SESS_LAST_ADV_ID"] : "null",
			"HIT_ID" => ($HIT_ID > 0 ? $HIT_ID : "NULL"),
			"COUNTRY_ID" => "'".$DB->ForSql($_SESSION["SESS_COUNTRY_ID"],2)."'",
			"KEEP_DAYS" => $sql_KEEP_DAYS,
			"CHARGEBACK" => "'".$chargeback."'",
			"SITE_ID" => $sql_site,
			"REFERER_SITE_ID" => $sql_referer_site_id,
		);

		if (intval($_SESSION["SESS_LAST_ADV_ID"])>0 && intval($_SESSION["SESS_ADV_ID"])<=0)
			$arFields["ADV_BACK"]="'Y'";

		$eid = $DB->Insert("b_stat_event_list", $arFields);

		// in case of first occurence
		if ($arEventType["DATE_ENTER"] == '')
		{
			// set date of the first event
			$arFields =  Array("DATE_ENTER"=>$DB->GetNowFunction());
			$DB->Update("b_stat_event",$arFields,"WHERE ID='".$EVENT_ID."'");
		}

		// day counter update
		$arFields = Array(
			"DATE_LAST" => $DB->GetNowFunction(),
			"COUNTER" => "COUNTER + 1",
			"MONEY" => "MONEY + ".$money
		);
		$rows = $DB->Update("b_stat_event_day",$arFields,"WHERE EVENT_ID='".$EVENT_ID."' and ".CStatistics::DBDateCompare("DATE_STAT"));
		// there was no records updated
		if (intval($rows)<=0)
		{
			// so add one
			$arFields_i = Array(
				"DATE_STAT" => $DB->GetNowDate(),
				"DATE_LAST" => $DB->GetNowFunction(),
				"EVENT_ID" => $EVENT_ID,
				"COUNTER" => 1,
				"MONEY" => $money
			);
			$DB->Insert("b_stat_event_day",$arFields_i);
		}
		elseif (intval($rows)>1) // more than one record for event
		{
			// delete
			$strSql = "SELECT ID FROM b_stat_event_day WHERE EVENT_ID='".$EVENT_ID."' and  ".CStatistics::DBDateCompare("DATE_STAT")." ORDER BY ID";
			$i = 0;
			$rs = $DB->Query($strSql);
			while ($ar = $rs->Fetch())
			{
				$i++;
				if ($i > 1)
				{
					$strSql = "DELETE FROM b_stat_event_day WHERE ID = ".$ar["ID"];
					$DB->Query($strSql);
				}
			}
		}

		// guest counter
		$arFields = Array("C_EVENTS" => "C_EVENTS+1");
		$DB->Update("b_stat_guest", $arFields, "WHERE ID=".intval($_SESSION["SESS_GUEST_ID"]), '',false,false,false);

		// session counter
		$arFields = Array("C_EVENTS" => "C_EVENTS+1");
		$DB->Update("b_stat_session", $arFields, "WHERE ID=".intval($_SESSION["SESS_SESSION_ID"]), '',false,false,false);

		// events counter
		$arFields = Array("C_EVENTS" => "C_EVENTS+1");
		$DB->Update("b_stat_day", $arFields, "WHERE ".CStatistics::DBDateCompare("DATE_STAT"), '',false,false,false);

		// when site defined
		if ($site_id <> '')
		{
			// update site
			$arFields = Array("C_EVENTS" => "C_EVENTS+1");
			$DB->Update("b_stat_day_site", $arFields, "WHERE SITE_ID='".$DB->ForSql($site_id,2)."' and ".CStatistics::DBDateCompare("DATE_STAT"));
		}

		// there is advertising compaign defined
		if (intval($_SESSION["SESS_ADV_ID"])>0 || intval($_SESSION["SESS_LAST_ADV_ID"])>0)
		{
			// increase revenue
			if ($money!=0)
			{
				$sign = ($chargeback=="Y") ? "-" : "+";
				$arFields = array(
					"REVENUE" => "REVENUE ".$sign." ".$money,
				);
				$DB->Update("b_stat_adv", $arFields, "WHERE ID='".intval($_SESSION["SESS_LAST_ADV_ID"])."'",'',false,false,false);
			}

			if (intval($_SESSION["SESS_ADV_ID"])>0)
			{
				$arFields = Array(
					"COUNTER" => "COUNTER + 1",
					"MONEY" => "MONEY + ".$money
				);
			}
			else
			{
				$arFields = Array(
					"COUNTER_BACK" => "COUNTER_BACK + 1",
					"MONEY_BACK" => "MONEY_BACK + ".$money
				);
			}

			$rows = $DB->Update("b_stat_adv_event",$arFields,"WHERE ADV_ID='".intval($_SESSION["SESS_LAST_ADV_ID"])."' and EVENT_ID='".$EVENT_ID."'");
			if(intval($rows) <= 0)
			{
				$arFields = Array(
					"ADV_ID" => "'".$_SESSION["SESS_LAST_ADV_ID"]."'",
					"EVENT_ID" => "'".$EVENT_ID."'",
				);
				if(intval($_SESSION["SESS_ADV_ID"]) > 0)
				{
					$arFields["COUNTER"] = "1";
					$arFields["MONEY"] = $money;
				}
				else
				{
					$arFields["COUNTER_BACK"] = "1";
					$arFields["MONEY_BACK"] = $money;
				}
				$DB->Insert("b_stat_adv_event", $arFields);
			}

			if (intval($_SESSION["SESS_ADV_ID"])>0)
			{
				$arFields = Array(
					"COUNTER" => "COUNTER + 1",
					"MONEY" => "MONEY + ".$money,
				);
			}
			else
			{
				$arFields = Array(
					"COUNTER_BACK" => "COUNTER_BACK + 1",
					"MONEY_BACK" => "MONEY_BACK + ".$money,
				);
			}

			$rows = $DB->Update("b_stat_adv_event_day",$arFields,"WHERE ADV_ID='".intval($_SESSION["SESS_LAST_ADV_ID"])."' and EVENT_ID='".$EVENT_ID."' and ".CStatistics::DBDateCompare("DATE_STAT"),'',false,false,false);
			if(intval($rows) <= 0)
			{
				$arFields = Array(
					"ADV_ID" => ($_SESSION["SESS_LAST_ADV_ID"]>0) ? $_SESSION["SESS_LAST_ADV_ID"] : "null",
					"EVENT_ID" => "'".$EVENT_ID."'",
					"DATE_STAT" => $DB->GetNowDate(),
				);
				if(intval($_SESSION["SESS_ADV_ID"]) > 0)
				{
					$arFields["COUNTER"] = "1";
					$arFields["MONEY"] = $money;
				}
				else
				{
					$arFields["COUNTER_BACK"] = "1";
					$arFields["MONEY_BACK"] = $money;
				}
				$DB->Insert("b_stat_adv_event_day", $arFields);
			}
			elseif(intval($rows) > 1)
			{
				$strSql = "SELECT ID FROM b_stat_adv_event_day WHERE ADV_ID='".intval($_SESSION["SESS_LAST_ADV_ID"])."' and EVENT_ID='".$EVENT_ID."' and ".CStatistics::DBDateCompare("DATE_STAT")." ORDER BY ID";
				$i = 0;
				$rs = $DB->Query($strSql);
				while ($ar = $rs->Fetch())
				{
					$i++;
					if ($i>1)
					{
						$strSql = "DELETE FROM b_stat_adv_event_day WHERE ID = ".$ar["ID"];
						$DB->Query($strSql);
					}
				}
			}
		}

		// todays traffic counters
		CTraffic::IncParam(array("EVENT" => 1), array("EVENT" => 1));

		if ($_SESSION["SESS_COUNTRY_ID"] <> '')
			CStatistics::UpdateCountry($_SESSION["SESS_COUNTRY_ID"], Array("C_EVENTS" => 1));
		if ($_SESSION["SESS_CITY_ID"] > 0)
			CStatistics::UpdateCity($_SESSION["SESS_CITY_ID"], Array("C_EVENTS" => 1));

		return array("EVENT_ID"=>intval($EVENT_ID), "TYPE_ID"=>intval($EVENT_ID), "EID"=>intval($eid));
	}

	// creates new event by ID
	public static function AddByID($EVENT_ID, $EVENT3, $DATE_ENTER, $PARAM, $MONEY="", $CURRENCY="", $CHARGEBACK="N")
	{
		return CStatEvent::Add($EVENT_ID, $EVENT3, $DATE_ENTER, $PARAM, $MONEY, $CURRENCY, $CHARGEBACK);
	}

	// creates new event by event1 and event2
	public static function AddByEvents($EVENT1, $EVENT2, $EVENT3, $DATE_ENTER, $PARAM, $MONEY="", $CURRENCY="", $CHARGEBACK="N")
	{
		$EVENT_ID = CStatEvent::SetEventType($EVENT1, $EVENT2, $arEventType);
		if ($EVENT_ID>0 && $PARAM <> '')
		{
			return CStatEvent::Add($EVENT_ID, $EVENT3, $DATE_ENTER, $PARAM, $MONEY, $CURRENCY, $CHARGEBACK);
		}
		return 0;
	}

	public static function GetHandlerList(&$arUSER_HANDLERS)
	{
		$arr = array();
		$arReferenceId = array();
		$arReference = array();
		$arUSER_HANDLERS = array();
		$i=0;

		// system loaders
		$path = COption::GetOptionString("statistic", "EVENTS_LOAD_HANDLERS_PATH");
		$handle=@opendir($_SERVER["DOCUMENT_ROOT"].$path);
		if($handle)
		{
			while (false!==($fname = readdir($handle)))
			{
				if (is_file($_SERVER["DOCUMENT_ROOT"].$path.$fname) && $fname!="." && $fname!="..")
				{
					$arReferenceId[] = $path.$fname;
					$arReference[] = $fname;
				}
			}
			closedir($handle);
		}

		// user defined loaders
		$path = "";
		$path = COption::GetOptionString("statistic", "USER_EVENTS_LOAD_HANDLERS_PATH");
		$handle=@opendir($_SERVER["DOCUMENT_ROOT"].$path);
		if($handle)
		{
			while (false!==($fname = readdir($handle)))
			{
				if (is_file($_SERVER["DOCUMENT_ROOT"].$path.$fname) && $fname!="." && $fname!="..")
				{
					$i++;
					$arReferenceId[] = $path.$fname;
					$arUSER_HANDLERS[] = $path.$fname;
					$arReference[] = "[".$i."] ".$fname;
				}
			}
			closedir($handle);
		}

		$arr = array("reference"=>$arReference,"reference_id"=>$arReferenceId);
		return $arr;
	}

	// decodes EVENT_GID into array
	public static function DecodeGID($EVENT_GID)
	{
		$ar = explode(".",$EVENT_GID);
		$sid = intval($ar[1]);
		$gid = intval($ar[2]);
		$base64 = "Y";

		if ((count($ar)==6 || count($ar)==7) && $sid>0 && $gid>0 && mb_strlen($ar[1]) == mb_strlen($sid) && mb_strlen($ar[2]) == mb_strlen($gid)) $base64 = "N";
		if ($base64=="Y")
		{
			$group_site_id = GetStatGroupSiteID();
			$s = mb_substr($EVENT_GID, mb_strlen($group_site_id) + 1, mb_strlen($EVENT_GID));
			$EVENT_GID = $group_site_id.".".base64_decode($s);
		}
		$arr = explode(".",$EVENT_GID);
		$SESSION_ID = (intval($arr[1])>0) ? intval($arr[1]) : "";
		$GUEST_ID = (intval($arr[2])>0) ? intval($arr[2]) : "";
		$COUNTRY_ID = ($arr[3] <> '') ? $arr[3] : "";
		$ADV_ID = (intval($arr[4])>0) ? intval($arr[4]) : "";
		$ADV_BACK = ($arr[5]=="Y" || $arr[5]=="N") ? $arr[5] : "";
		$SITE_ID = ($arr[6] <> '') ? $arr[6] : "";

		$arrRes = array(
			"SESSION_ID" => $SESSION_ID,
			"GUEST_ID" => $GUEST_ID,
			"COUNTRY_ID" => $COUNTRY_ID,
			"ADV_ID" => $ADV_ID,
			"ADV_BACK" => $ADV_BACK,
			"SITE_ID" => $SITE_ID,
		);
		return $arrRes;
	}

	// compatibility
	public static function SetEventType($event1, $event2, &$arEventType)
	{
		return CStatEventType::ConditionSet($event1, $event2, $arEventType);
	}

	public static function GetByEvents($event1, $event2)
	{
		return CStatEventType::GetByEvents($event1, $event2);
	}

	public static function GetEventsByGuest($GUEST_ID, $EVENT_ID=false, $EVENT3=false, $SEC=false)
	{
		return CStatEvent::GetListByGuest($GUEST_ID, $EVENT_ID, $EVENT3, $SEC);
	}

	public static function GetListUniqueCheck($arFilter=Array(), $LIMIT=1)
	{
		$DB = CDatabase::GetModuleConnection('statistic');
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
					if( ((string)$val == '') || ($val === "NOT_REF") )
						continue;
				}
				$match_value_set = array_key_exists($key."_EXACT_MATCH", $arFilter);
				$key = strtoupper($key);
				switch($key)
				{
					case "EVENT3":
						$arSqlSearch[] = "E.EVENT3 = '".$DB->ForSql($val,255)."'";
						break;
					case "DATE":
						if (CheckDateTime($val))
							$arSqlSearch[] = "E.DATE_ENTER=".$DB->CharToDateFunction($val);
						break;
					case "EVENT_ID":
					case "SESSION_ID":
					case "GUEST_ID":
					case "ADV_ID":
					case "COUNTRY_ID":
						$arSqlSearch[] = "E.".$key."='".$DB->ForSql($val)."'";
						break;
					case "ADV_BACK":
						$arSqlSearch[] = ($val=="Y") ? "E.ADV_BACK='Y'" : "E.ADV_BACK='N'";
						break;
					case "SITE_ID":
						$arSqlSearch[] = "E.SITE_ID = '".$DB->ForSql($val,2)."'";
						break;
				}
			}
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT /*TOP*/
				E.ID
			FROM
				b_stat_event_list E
			WHERE
				".$strSqlSearch."
		";

		$res = $DB->Query(CStatistics::DBTopSql($strSql, $LIMIT));
		return $res;
	}
}
