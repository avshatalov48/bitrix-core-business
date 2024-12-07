<?php

class CFormStatus extends CAllFormStatus
{
	public static function GetList($FORM_ID, $by = 's_sort', $order = 'asc', $arFilter = [])
	{
		global $DB, $strError;
		$FORM_ID = intval($FORM_ID);
		$arSqlSearch = Array();
		$arSqlSearch_h = [];
		$strSqlSearch_h = '';
		if (is_array($arFilter))
		{
			$filter_keys = array_keys($arFilter);
			$keyCount = count($filter_keys);
			for ($i=0; $i<$keyCount; $i++)
			{
				$key = $filter_keys[$i];
				$val = $arFilter[$filter_keys[$i]];
				if ((string)$val == '' || $val=="NOT_REF")
					continue;
				if (is_array($val) && empty($val))
					continue;
				$match_value_set = (in_array($key."_EXACT_MATCH", $filter_keys));
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("S.ID",$val,$match);
						break;
					case "ACTIVE":
						$arSqlSearch[] = ($val=="Y") ? "S.ACTIVE='Y'" : "S.ACTIVE='N'";
						break;
					case "TITLE":
					case "DESCRIPTION":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("S.".$key, $val, $match);
						break;
					case "RESULTS_1":
						$arSqlSearch_h[] = "count(R.ID)>='".intval($val)."'";
						break;
					case "RESULTS_2":
						$arSqlSearch_h[] = "count(R.ID)<='".intval($val)."'";
						break;
				}
			}
			if (!empty($arSqlSearch_h))
			{
				$strSqlSearch_h = ' and ('.implode(') and (', $arSqlSearch_h).') ';
			}
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		if ($by == "s_id")					$strSqlOrder = "ORDER BY S.ID";
		elseif ($by == "s_timestamp")		$strSqlOrder = "ORDER BY S.TIMESTAMP_X";
		elseif ($by == "s_active")			$strSqlOrder = "ORDER BY S.ACTIVE";
		elseif ($by == "s_c_sort" ||
				$by == "s_sort")			$strSqlOrder = "ORDER BY S.C_SORT";
		elseif ($by == "s_default")			$strSqlOrder = "ORDER BY S.DEFAULT_VALUE";
		elseif ($by == "s_title")			$strSqlOrder = "ORDER BY S.TITLE ";
		elseif ($by == "s_description")		$strSqlOrder = "ORDER BY S.DESCRIPTION";
		elseif ($by == "s_results")			$strSqlOrder = "ORDER BY RESULTS";
		else
		{
			$strSqlOrder = "ORDER BY S.C_SORT";
		}

		if ($order!="desc")
		{
			$strSqlOrder .= " asc ";
		}
		else $strSqlOrder .= " desc ";

		$strSql = "
			SELECT
				S.ID, S.CSS, S.FORM_ID, S.C_SORT, S.ACTIVE, S.TITLE, S.DESCRIPTION, S.DEFAULT_VALUE, S.HANDLER_OUT, S.HANDLER_IN,
				".$DB->DateToCharFunction("S.TIMESTAMP_X")."	TIMESTAMP_X,
				count(distinct R.ID) RESULTS
			FROM
				b_form_status S
			LEFT JOIN b_form_result R ON (R.STATUS_ID = S.ID and R.FORM_ID=S.FORM_ID)
			WHERE
			$strSqlSearch
			and S.FORM_ID = $FORM_ID
			GROUP BY S.ID
			HAVING
				1=1
				$strSqlSearch_h
			$strSqlOrder
			";
		$res = $DB->Query($strSql);

		return $res;
	}

	public static function GetByID($ID)
	{
		global $DB, $strError;
		$ID = intval($ID);
		$strSql = "
			SELECT
				S.ID, S.CSS, S.FORM_ID, S.C_SORT, S.ACTIVE, S.TITLE, S.DESCRIPTION, S.DEFAULT_VALUE, S.HANDLER_OUT, S.HANDLER_IN, S.MAIL_EVENT_TYPE, 
				".$DB->DateToCharFunction("S.TIMESTAMP_X")." TIMESTAMP_X,
				count(distinct R.ID) RESULTS
			FROM
				b_form_status S
			LEFT JOIN b_form_result R ON (R.STATUS_ID = S.ID and R.FORM_ID=S.FORM_ID)
			WHERE
				S.ID = $ID
			GROUP BY S.ID
			";
		$res = $DB->Query($strSql);
		return $res;
	}

	public static function GetDropdown($FORM_ID, $PERMISSION = array("MOVE"), $OWNER_ID=0)
	{
		global $DB, $USER, $strError;
		$FORM_ID = intval($FORM_ID);
		if (CForm::IsAdmin())
		{
			$strSql = "
				SELECT
					S.ID								REFERENCE_ID,
					concat('[',S.ID,'] ',S.TITLE)		REFERENCE
				FROM
					b_form_status S
				WHERE
					S.FORM_ID = $FORM_ID
				and S.ACTIVE = 'Y'
				ORDER BY S.C_SORT
				";
		}
		else
		{
			if (is_array($PERMISSION)) $arrPERMISSION = $PERMISSION;
			else
			{
				if (intval($PERMISSION)==2) $PERMISSION = "MOVE";
				if (intval($PERMISSION)==1) $PERMISSION = "VIEW, MOVE";
				$arrPERMISSION = explode(",",$PERMISSION);
			}
			$str = "''";
			$arrPERM = array();
			if (is_array($arrPERMISSION) && count($arrPERMISSION)>0)
			{
				foreach ($arrPERMISSION as $perm)
				{
					$arrPERM[] = trim($perm);
					$str .= ",'".$DB->ForSql(trim($perm))."'";
				}
			}
			$arGroups = $USER->GetUserGroupArray();
			if (!is_array($arGroups)) $arGroups[] = 2;
			if ($OWNER_ID==$USER->GetID() || (in_array("VIEW",$arrPERM) && in_array("MOVE",$arrPERM))) $arGroups[] = 0;
			if (is_array($arGroups) && count($arGroups)>0) $groups = implode(",",$arGroups);
			$strSql = "
				SELECT
					S.ID								REFERENCE_ID,
					concat('[',S.ID,'] ',S.TITLE)		REFERENCE
				FROM
					b_form_status S,
					b_form_status_2_group G
				WHERE
					S.FORM_ID = $FORM_ID
				and S.ACTIVE = 'Y'
				and G.STATUS_ID = S.ID
				and G.GROUP_ID in ($groups)
				and G.PERMISSION in ($str)
				GROUP BY
					S.ID, S.TITLE
				ORDER BY S.C_SORT
				";
		}
		$z = $DB->Query($strSql);
		return $z;
	}
}
