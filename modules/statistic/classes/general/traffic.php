<?php

class CAllTraffic
{
	public static function DynamicDays($date1="", $date2="", $site_id="")
	{
		$arMaxMin = array();
		$is_filtered = false;
		$z = CTraffic::GetDailyList('', '', $arMaxMin, array("DATE1"=>$date1, "DATE2"=>$date2, "SITE_ID"=>$site_id));
		$d = 0;
		while($zr = $z->Fetch())
			$d++;
		return $d;
	}

	// updates traffic counters
	public static function DecParam($arParam, $arParamSite=false, $SITE_ID=false, $DATE=false, $DATE_FORMAT="FULL")
	{
		return CTraffic::IncParam($arParam, $arParamSite, $SITE_ID, $DATE, $DATE_FORMAT, "-");
	}

	// updates traffic counters
	public static function IncParam($arParam, $arParamSite=false, $SITE_ID=false, $DATE=false, $DATE_FORMAT="FULL", $SIGN="+")
	{
		$DB = CDatabase::GetModuleConnection('statistic');

		if($DATE==false)
		{
			$strWhere = "WHERE ".CStatistics::DBDateCompare("DATE_STAT");
			$stmp = time();
		}
		else
		{
			$stmp = MakeTimeStamp($DATE, $DATE_FORMAT=="SHORT" ? FORMAT_DATE : FORMAT_DATETIME);
			$strWhere = "WHERE ".CStatistics::DBDateCompare("DATE_STAT", ConvertTimeStamp($stmp));
		}

		$arKeys = array(
			"HOUR" => date("G",$stmp),
			"WEEKDAY" => date("w",$stmp),
			"MONTH" => date("n",$stmp),
		);
		static $arPreKeys = array("HITS"=>0,"FAVORITES"=>0,"SESSIONS"=>0,"C_HOSTS"=>0,"GUESTS"=>0,"NEW_GUESTS"=>0);

		$rows = false;
		if (is_array($arParam) && count($arParam)>0)
		{
			if(array_key_exists("TOTAL_HOSTS", $arParam))
				unset($arParam["TOTAL_HOSTS"]);
			$arFields = array();
			foreach($arParam as $name=>$value)
			{
				if(array_key_exists($name, $arPreKeys))
				{
					$arFields[$name] = $name." + ".intval($value);
				}
				else
				{
					foreach ($arKeys as $key => $v)
					{
						$k = $key."_".$name."_".$v;
						$arFields[$k] = "$k ".($SIGN==="-"? "-": "+")." ".intval($value);
					}
				}
			}

			if (count($arFields)>0)
				$rows = $DB->Update("b_stat_day", $arFields, $strWhere);
		}

		if ($SITE_ID===false)
		{
			$SITE_ID = "";
			if (defined("ADMIN_SECTION") && ADMIN_SECTION===true)
				$SITE_ID = "";
			elseif (defined("SITE_ID"))
				$SITE_ID = SITE_ID;
		}

		if ($SITE_ID <> '' && is_array($arParamSite) && count($arParamSite)>0)
		{
			$arFields = array();
			foreach($arParamSite as $name=>$value)
			{
				if(array_key_exists($name, $arPreKeys))
				{
					$arFields[$name] = $name." + ".intval($value);
				}
				else
				{
					foreach ($arKeys as $key => $v)
					{
						$k = $key."_".$name."_".$v;
						$arFields[$k] = "$k ".($SIGN==="-"? "-": "+")." ".intval($value);
					}
				}
			}
			if (count($arFields)>0)
				$rows = $DB->Update("b_stat_day_site", $arFields, $strWhere." AND SITE_ID='".$DB->ForSql($SITE_ID,2)."'");
		}
		return $rows;
	}
}
