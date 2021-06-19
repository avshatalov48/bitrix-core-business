<?php

class CForm extends CAllForm
{
	public static function err_mess()
	{
		$module_id = "form";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: CForm<br>File: ".__FILE__;
	}

	public static function GetList($by = 's_sort', $order = 'asc', $arFilter = [], $is_filtered = null, $min_permission = 10)
	{
		$err_mess = (CForm::err_mess())."<br>Function: GetList<br>Line: ";
		global $DB, $USER, $strError;
		$min_permission = intval($min_permission);

		$arSqlSearch = Array();
		if (is_array($arFilter))
		{
			if ($arFilter["SID"] <> '') $arFilter["VARNAME"] = $arFilter["SID"];
			elseif ($arFilter["VARNAME"] <> '') $arFilter["SID"] = $arFilter["VARNAME"];

			$filter_keys = array_keys($arFilter);
			$keyCount = count($filter_keys);
			for ($i=0; $i<$keyCount; $i++)
			{
				$key = $filter_keys[$i];
				$val = $arFilter[$filter_keys[$i]];
				if(is_array($val))
				{
					if(empty($val))
						continue;
				}
				else
				{
					if((string)$val == '' || $val === "NOT_REF")
						continue;
				}
				$match_value_set = (in_array($key."_EXACT_MATCH", $filter_keys));
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
					case "SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("F.".$key, $val, $match);
						break;
					case "NAME":
					case "DESCRIPTION":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("F.".$key, $val, $match);
						break;
					case "SITE":
						if (is_array($val)) $val = implode(" | ", $val);
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("FS.SITE_ID", $val, $match);
						$left_join = "LEFT JOIN b_form_2_site FS ON (F.ID = FS.FORM_ID)";
						break;
				}
			}
		}

		if ($by == "s_id")								$strSqlOrder = "ORDER BY F.ID";
		elseif ($by == "s_c_sort" || $by == "s_sort")	$strSqlOrder = "ORDER BY F.C_SORT";
		elseif ($by == "s_name")						$strSqlOrder = "ORDER BY F.NAME";
		elseif ($by == "s_varname" || $by == "s_sid")	$strSqlOrder = "ORDER BY F.SID";
		else
		{
			$strSqlOrder = "ORDER BY F.C_SORT";
		}

		if ($order!="desc")
		{
			$strSqlOrder .= " asc ";
		}
		else
		{
			$strSqlOrder .= " desc ";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		if (CForm::IsAdmin())
		{
			$strSql = "
				SELECT
					F.*,
					F.SID											VARNAME,
					F.FIRST_SITE_ID,
					F.FIRST_SITE_ID									LID,
					".$DB->DateToCharFunction("F.TIMESTAMP_X")."	TIMESTAMP_X,
					count(distinct D1.ID)							C_FIELDS,
					count(distinct D2.ID)							QUESTIONS,
					count(distinct S.ID)							STATUSES
				FROM
					b_form F
				LEFT JOIN b_form_status S ON (S.FORM_ID = F.ID)
				LEFT JOIN b_form_field D1 ON (D1.FORM_ID = F.ID and D1.ADDITIONAL='Y')
				LEFT JOIN b_form_field D2 ON (D2.FORM_ID = F.ID and D2.ADDITIONAL<>'Y')
				$left_join
				WHERE
				$strSqlSearch
				GROUP BY F.ID
				$strSqlOrder
				";
		}
		else
		{
			$arGroups = $USER->GetUserGroupArray();
			if (!is_array($arGroups)) $arGroups[] = 2;
			$groups = implode(",",$arGroups);
			$def_permission = COption::GetOptionInt("form", "FORM_DEFAULT_PERMISSION", 10);
			$strSql = "
				SELECT
					F.*,
					F.SID VARNAME,
					F.FIRST_SITE_ID,
					F.FIRST_SITE_ID LID,
					".$DB->DateToCharFunction("F.TIMESTAMP_X")."	TIMESTAMP_X,
					count(distinct D1.ID) C_FIELDS,
					count(distinct D2.ID) QUESTIONS,
					count(distinct S.ID) STATUSES
				FROM
					b_form F
					".
					($def_permission >=$min_permission?
					"	LEFT JOIN b_form_2_group G ON (G.FORM_ID=F.ID and G.GROUP_ID in ($groups)) "
					:
					"	INNER JOIN b_form_2_group G ON (G.FORM_ID=F.ID and G.PERMISSION>=$min_permission and G.GROUP_ID in ($groups))	"
					)."
				LEFT JOIN b_form_status S ON (S.FORM_ID = F.ID)
				LEFT JOIN b_form_field D1 ON (D1.FORM_ID = F.ID and D1.ADDITIONAL='Y')
				LEFT JOIN b_form_field D2 ON (D2.FORM_ID = F.ID and D2.ADDITIONAL<>'Y')
				$left_join
				WHERE $strSqlSearch ".
				($def_permission >=$min_permission?
				"	AND (G.FORM_ID IS NULL OR G.PERMISSION>=$min_permission) "
				:
				""
				).
				"

				GROUP BY F.ID
				$strSqlOrder
				";
		}

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		return $res;
	}

	public static function GetByID($ID, $GET_BY_SID="N")
	{
		$err_mess = (CForm::err_mess())."<br>Function: GetByID<br>Line: ";
		global $DB, $strError;
		$where = ($GET_BY_SID=="N") ? " F.ID = '".intval($ID)."' " : " F.SID='".$DB->ForSql($ID,50)."' ";
		$strSql = "
			SELECT
				F.*,
				F.FIRST_SITE_ID,
				F.FIRST_SITE_ID									LID,
				F.SID,
				F.SID											VARNAME,
				".$DB->DateToCharFunction("F.TIMESTAMP_X")."	TIMESTAMP_X,
				count(distinct D1.ID)							C_FIELDS,
				count(distinct D2.ID)							QUESTIONS,
				count(distinct S.ID)							STATUSES
			FROM b_form F
			LEFT JOIN b_form_status S ON (S.FORM_ID = F.ID)
			LEFT JOIN b_form_field D1 ON (D1.FORM_ID = F.ID and D1.ADDITIONAL='Y')
			LEFT JOIN b_form_field D2 ON (D2.FORM_ID = F.ID and D2.ADDITIONAL<>'Y')
			WHERE
				$where
			GROUP BY
				F.ID
			";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetFormTemplateByID($ID, $GET_BY_SID="N")
	{
		$err_mess = (CForm::err_mess())."<br>Function: GetFormTemplateByID<br>Line: ";
		global $DB, $strError;
		$where = ($GET_BY_SID=="N") ? " F.ID = '".intval($ID)."' " : " F.SID='".$DB->ForSql($ID,50)."' ";
		$strSql = "
			SELECT
				F.FORM_TEMPLATE FT
			FROM b_form F
			WHERE
				$where
			";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		if ($arRes = $res->Fetch()) return $arRes["FT"];
		else return "";
	}
}
