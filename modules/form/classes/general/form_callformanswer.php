<?php

class CAllFormAnswer
{
	public static function Copy($ID, $NEW_QUESTION_ID=false)
	{
		global $DB, $APPLICATION, $strError;
		$ID = intval($ID);
		$NEW_QUESTION_ID = intval($NEW_QUESTION_ID);
		$rsAnswer = CFormAnswer::GetByID($ID);
		if ($arAnswer = $rsAnswer->Fetch())
		{
			$arFields = array(
				"QUESTION_ID"	=> ($NEW_QUESTION_ID>0) ? $NEW_QUESTION_ID : $arAnswer["QUESTION_ID"],
				"MESSAGE"		=> $arAnswer["MESSAGE"],
				"VALUE"			=> $arAnswer["VALUE"],
				"C_SORT"		=> $arAnswer["C_SORT"],
				"ACTIVE"		=> $arAnswer["ACTIVE"],
				"FIELD_TYPE"	=> $arAnswer["FIELD_TYPE"],
				"FIELD_WIDTH"	=> $arAnswer["FIELD_WIDTH"],
				"FIELD_HEIGHT"	=> $arAnswer["FIELD_HEIGHT"],
				"FIELD_PARAM"	=> $arAnswer["FIELD_PARAM"],
				);
			$NEW_ID = CFormAnswer::Set($arFields);
			return $NEW_ID;
		}
		else $strError .= GetMessage("FORM_ERROR_ANSWER_NOT_FOUND")."<br>";
		return false;
	}

	public static function Delete($ID, $QUESTION_ID=false)
	{
		global $DB, $strError;
		$ID = intval($ID);
		$DB->Query("DELETE FROM b_form_answer WHERE ID='".$ID."'");
		if (intval($QUESTION_ID)>0) $str = " FIELD_ID = ".intval($QUESTION_ID)." and ";
		$DB->Query("DELETE FROM b_form_result_answer WHERE ".$str." ANSWER_ID='".$ID."'");
		return true;
	}

	public static function GetTypeList()
	{
		global $bSimple;
		$arrT = array(
				"text",
				"textarea",
				"radio",
				"checkbox",
				"dropdown",
				"multiselect",
				"date",
				"image",
				"file",
				"email",
				"url",
				"password",
				"hidden"
				);
		//if ($bSimple) $arrT[] = "hidden";
		$arr = array("reference_id" => $arrT, "reference" => $arrT);
		return $arr;
	}

	public static function GetList($QUESTION_ID, $by = 's_sort', $order = 'asc', $arFilter = [])
	{
		global $DB;
		$QUESTION_ID = intval($QUESTION_ID);
		$arSqlSearch = Array();
		if (is_array($arFilter))
		{
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
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("A.ID",$val,$match);
						break;
					case "MESSAGE":
					case "VALUE":
					case "FIELD_TYPE":
					case "FIELD_WIDTH":
					case "FIELD_HEIGHT":
					case "FIELD_PARAM":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("A.".$key, $val, $match);
						break;
					case "ACTIVE":
						$arSqlSearch[] = ($val=="Y") ? "A.ACTIVE='Y'" : "A.ACTIVE='N'";
						break;
				}
			}
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		if ($by == "s_id") $strSqlOrder = "ORDER BY A.ID";
		elseif ($by == "s_c_sort" || $by == "s_sort") $strSqlOrder = "ORDER BY A.C_SORT";
		else
		{
			$strSqlOrder = "ORDER BY A.C_SORT";
		}

		if ($order != "desc")
		{
			$strSqlOrder .= " asc ";
		}
		else
		{
			$strSqlOrder .= " desc ";
		}

		$strSql = "
			SELECT
				A.ID,
				A.FIELD_ID,
				A.FIELD_ID as QUESTION_ID,
				".$DB->DateToCharFunction("A.TIMESTAMP_X")."	TIMESTAMP_X,
				A.MESSAGE,
				A.VALUE,
				A.FIELD_TYPE,
				A.FIELD_WIDTH,
				A.FIELD_HEIGHT,
				A.FIELD_PARAM,
				A.C_SORT,
				A.ACTIVE
			FROM
				b_form_answer A
			WHERE
			$strSqlSearch
			and A.FIELD_ID = $QUESTION_ID
			$strSqlOrder
			";
		//echo "<pre>$strSql</pre>";
		$res = $DB->Query($strSql);

		return $res;
	}

	public static function GetByID($ID)
	{
		global $DB, $strError;
		$ID = intval($ID);
		$strSql = "
			SELECT
				A.ID,
				A.FIELD_ID,
				A.FIELD_ID as QUESTION_ID,
				".$DB->DateToCharFunction("A.TIMESTAMP_X")."	TIMESTAMP_X,
				A.MESSAGE,
				A.VALUE,
				A.FIELD_TYPE,
				A.FIELD_WIDTH,
				A.FIELD_HEIGHT,
				A.FIELD_PARAM,
				A.C_SORT,
				A.ACTIVE
			FROM
				b_form_answer A
			WHERE
				ID='$ID'
			";
		//echo $strSql;
		$res = $DB->Query($strSql);
		return $res;
	}

	public static function CheckFields($arFields, $ANSWER_ID=false)
	{
		global $strError;
		$str = "";
		$ANSWER_ID = intval($ANSWER_ID);

		if (intval($arFields["QUESTION_ID"] ?? 0) > 0) $arFields["FIELD_ID"] = $arFields["QUESTION_ID"];
		else $arFields["QUESTION_ID"] = $arFields["FIELD_ID"];

		if ($ANSWER_ID<=0 && intval($arFields["QUESTION_ID"])<=0)
		{
			$str .= GetMessage("FORM_ERROR_FORGOT_QUESTION_ID")."<br>";
		}

		if ($ANSWER_ID<=0 || ($ANSWER_ID>0 && is_set($arFields, "MESSAGE")))
		{
			if ($arFields["MESSAGE"] == '') $str .= GetMessage("FORM_ERROR_FORGOT_ANSWER_TEXT")."<br>";
		}

		$strError .= $str;
		if ($str <> '') return false; else return true;
	}

	public static function Set($arFields, $ANSWER_ID=false)
	{
		global $DB;

		$ANSWER_ID = intval($ANSWER_ID);

		if (CFormAnswer::CheckFields($arFields, $ANSWER_ID))
		{
			$arFields_i = array();

			$arFields_i["TIMESTAMP_X"] = $DB->GetNowFunction();

			if (is_set($arFields, "MESSAGE"))
				$arFields_i["MESSAGE"] = "'".$DB->ForSql($arFields["MESSAGE"],2000)."'";

			if (is_set($arFields, "VALUE"))
				$arFields_i["VALUE"] = "'".$DB->ForSql($arFields["VALUE"],2000)."'";

			if (is_set($arFields, "ACTIVE"))
				$arFields_i["ACTIVE"] = ($arFields["ACTIVE"]=="Y") ? "'Y'" : "'N'";

			if (is_set($arFields, "C_SORT"))
				$arFields_i["C_SORT"] = "'".intval($arFields["C_SORT"])."'";

			if (is_set($arFields, "FIELD_TYPE"))
				$arFields_i["FIELD_TYPE"] = "'".$DB->ForSql($arFields["FIELD_TYPE"],255)."'";

			if (is_set($arFields, "FIELD_WIDTH"))
				$arFields_i["FIELD_WIDTH"] = "'".intval($arFields["FIELD_WIDTH"])."'";

			if (is_set($arFields, "FIELD_HEIGHT"))
				$arFields_i["FIELD_HEIGHT"] = "'".intval($arFields["FIELD_HEIGHT"])."'";

			if (is_set($arFields, "FIELD_PARAM"))
				$arFields_i["FIELD_PARAM"] = "'".$DB->ForSql($arFields["FIELD_PARAM"],2000)."'";

			if ($ANSWER_ID>0)
			{
				$DB->Update("b_form_answer", $arFields_i, "WHERE ID='".$ANSWER_ID."'");

				$arFields_u = array();
				$arFields_u["ANSWER_TEXT"] = $arFields_i["MESSAGE"];
				$arFields_u["ANSWER_VALUE"] = $arFields_i["VALUE"];
				$DB->Update("b_form_result_answer", $arFields_u, "WHERE ANSWER_ID='".$ANSWER_ID."'");
			}
			else
			{
				if (intval($arFields["QUESTION_ID"])>0) $arFields["FIELD_ID"] = $arFields["QUESTION_ID"];
				else $arFields["QUESTION_ID"] = $arFields["FIELD_ID"];

				$arFields_i["FIELD_ID"] = "'".intval($arFields["QUESTION_ID"])."'";

				$ANSWER_ID = $DB->Insert("b_form_answer", $arFields_i);
				$ANSWER_ID = intval($ANSWER_ID);
			}
			return $ANSWER_ID;
		}
		return false;
	}
}
