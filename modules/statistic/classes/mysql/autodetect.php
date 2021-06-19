<?php

class CAutoDetect
{
	public static function GetList($by = 's_counter', $order = 'desc', $arFilter = [])
	{
		$err_mess = "File: ".__FILE__."<br>Line: ";
		$DB = CDatabase::GetModuleConnection('statistic');
		$arSqlSearch = Array();
		$arSqlSearch_h = Array();
		$strSqlSearch_h = "";
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
					case "LAST":
						$arSqlSearch[] = ($val=="Y") ? "S.DATE_STAT = curdate()" : "S.DATE_STAT<>curdate()";
						break;
					case "USER_AGENT":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("S.USER_AGENT",$val,$match);
						break;
					case "COUNTER1":
						$arSqlSearch_h[] = "COUNTER>=".intval($val);
						break;
					case "COUNTER2":
						$arSqlSearch_h[] = "COUNTER<=".intval($val);
						break;
				}
			}
			foreach($arSqlSearch_h as $sqlWhere)
				$strSqlSearch_h .= " and (".$sqlWhere.") ";
		}

		if ($by == "s_user_agent")
			$strSqlOrder = "ORDER BY S.USER_AGENT";
		elseif ($by == "s_counter")
			$strSqlOrder = "ORDER BY COUNTER";
		else
		{
			$strSqlOrder = "ORDER BY COUNTER";
		}

		if ($order != "asc")
		{
			$strSqlOrder .= " desc ";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);

		$strSql = "
			SELECT
				S.USER_AGENT,
				count(S.ID) COUNTER
			FROM
				b_stat_session S
			LEFT JOIN b_stat_browser B ON (
				length(B.USER_AGENT)>0
			and B.USER_AGENT is not null
			and	upper(S.USER_AGENT) like upper(B.USER_AGENT)
			)
			LEFT JOIN b_stat_searcher R ON (
				length(R.USER_AGENT)>0
			and	R.USER_AGENT is not null
			and	upper(S.USER_AGENT) like upper(concat('%',R.USER_AGENT,'%'))
			)
			WHERE
			$strSqlSearch
			and S.USER_AGENT is not null
			and S.USER_AGENT<>''
			and S.NEW_GUEST<>'N'
			and B.ID is null
			and R.ID is null
			GROUP BY S.USER_AGENT
			HAVING '1'='1' $strSqlSearch_h
			$strSqlOrder
		";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		return $res;
	}
}
