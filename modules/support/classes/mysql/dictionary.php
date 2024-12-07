<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/classes/general/dictionary.php");

class CTicketDictionary extends CAllTicketDictionary
{
	public static function GetList($by = 's_c_sort', $order = 'asc', $arFilter = [])
	{
		global $DB;
		$arSqlSearch = Array();
		$leftJoinSite = "";
		$leftJoinUser = "";
		if (is_array($arFilter))
		{
			$filterKeys = array_keys($arFilter);
			$filterKeysCount = count($filterKeys);
			for ($i=0; $i<$filterKeysCount; $i++)
			{
				$key = $filterKeys[$i];
				$val = $arFilter[$filterKeys[$i]];
				if ((is_array($val) && count($val)<=0) || (!is_array($val) && ((string) $val == '' || $val==='NOT_REF')))
					continue;
				$match_value_set = (in_array($key."_EXACT_MATCH", $filterKeys)) ? true : false;
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
					case "SID":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("D.".$key, $val, $match);
						break;
					case "SITE":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("DS.SITE_ID", $val, $match);
						$leftJoinSite .= "LEFT JOIN b_ticket_dictionary_2_site DS ON (D.ID = DS.DICTIONARY_ID)";
						$select_user = ", DS.SITE_ID ";
						break;
					case "TYPE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("D.C_TYPE", $val, $match);
						break;
					case "NAME":
					case "DESCR":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("D.".$key, $val, $match);
						break;
					case "RESPONSIBLE_ID":
						if (intval($val)>0) $arSqlSearch[] = "D.RESPONSIBLE_USER_ID = '".intval($val)."'";
						elseif ($val==0) $arSqlSearch[] = "(D.RESPONSIBLE_USER_ID is null or D.RESPONSIBLE_USER_ID=0)";
						break;
					case "RESPONSIBLE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("D.RESPONSIBLE_USER_ID, U.LOGIN, U.LAST_NAME, U.NAME", $val, $match);
						$select_user = ",
							U.LOGIN														RESPONSIBLE_LOGIN,
							concat(ifnull(U.NAME,''),' ',ifnull(U.LAST_NAME,''))		RESPONSIBLE_NAME
							";
						$leftJoinUser = "LEFT JOIN b_user U ON (U.ID = D.RESPONSIBLE_USER_ID)";
						break;
					case "DEFAULT":
						$arSqlSearch[] = ($val=="Y") ? "D.SET_AS_DEFAULT='Y'" : "D.SET_AS_DEFAULT='N'";
						break;
					case "LID":
					case "FIRST_SITE_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("D.FIRST_SITE_ID",$val,$match);
						break;
				}
			}
		}

		if ($by == "s_id")
		{
			$strSqlOrder = "D.ID";
		}
		elseif ($by == "s_c_sort")
		{
			$strSqlOrder = "D.C_SORT";
		}
		elseif ($by == "s_sid")
		{
			$strSqlOrder = "D.SID";
		}
		elseif ($by == "s_lid")
		{
			$strSqlOrder = "D.FIRST_SITE_ID";
		}
		elseif ($by == "s_name")
		{
			$strSqlOrder = "D.NAME";
		}
		elseif ($by == "s_responsible")
		{
			$strSqlOrder = "D.RESPONSIBLE_USER_ID";
		}
		elseif ($by == "s_dropdown")
		{
			$strSqlOrder = "D.C_SORT, D.ID, D.NAME";
		}
		else
		{
			$strSqlOrder = "D.C_SORT";
		}

		if ($order == "desc")
		{
			$strSqlOrder .= " desc ";
		}
		else
		{
			$strSqlOrder .= " asc ";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				D.*,
				D.FIRST_SITE_ID						LID,
				D.ID								REFERENCE_ID,
				D.NAME								REFERENCE
				$select_user
			FROM
				b_ticket_dictionary D
			$leftJoinUser
			$leftJoinSite
			WHERE
			$strSqlSearch
			GROUP BY
				D.ID
			ORDER BY
				case D.C_TYPE
					when 'C'	then '1'
					when 'F'	then '2'
					when 'S'	then '3'
					when 'M'	then '4'
					when 'K'	then '5'
					when 'SR'	then '6'
					when 'D'	then '7'
					else ''	end,
			$strSqlOrder
			";
		$res = $DB->Query($strSql);

		return $res;
	}
}
