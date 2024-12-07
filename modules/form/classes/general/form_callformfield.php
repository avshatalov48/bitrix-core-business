<?php

class CAllFormField
{
	public static function GetList($WEB_FORM_ID, $get_fields, $by = 's_sort', $order = 'asc', $arFilter = [])
	{
		global $DB, $strError;
		$WEB_FORM_ID = intval($WEB_FORM_ID);
		$str = "";
		if ($get_fields <> '' && $get_fields!="ALL")
		{
			InitBVar($get_fields);
			$str = "and ADDITIONAL='$get_fields'";
		}
		$arSqlSearch = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			if(isset($arFilter["SID"]) && $arFilter["SID"] <> '')
			{
				$arFilter["VARNAME"] = $arFilter["SID"];
			}
			elseif(isset($arFilter["VARNAME"]) && $arFilter["VARNAME"] <> '')
			{
				$arFilter["SID"] = $arFilter["VARNAME"];
			}

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
					case "SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("F.".$key, $val, $match);
						break;
					case "TITLE":
					case "COMMENTS":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("F.".$key, $val, $match);
						break;
					case "ACTIVE":
					case "IN_RESULTS_TABLE":
					case "IN_EXCEL_TABLE":
					case "IN_FILTER":
					case "REQUIRED":
						$arSqlSearch[] = ($val=="Y") ? "F.".$key."='Y'" : "F.".$key."='N'";
						break;
				}
			}
		}
		if ($by == "s_id")						$strSqlOrder = "ORDER BY F.ID";
		elseif ($by == "s_active")				$strSqlOrder = "ORDER BY F.ACTIVE";
		elseif ($by == "s_varname" ||
				$by == "s_sid")					$strSqlOrder = "ORDER BY F.SID";
		elseif ($by == "s_c_sort" ||
				$by == "s_sort")				$strSqlOrder = "ORDER BY F.C_SORT";
		elseif ($by == "s_title")				$strSqlOrder = "ORDER BY F.TITLE";
		elseif ($by == "s_comments")			$strSqlOrder = "ORDER BY F.COMMENTS";
		elseif ($by == "s_required")			$strSqlOrder = "ORDER BY F.REQUIRED";
		elseif ($by == "s_in_results_table")	$strSqlOrder = "ORDER BY F.IN_RESULTS_TABLE";
		elseif ($by == "s_in_excel_table")		$strSqlOrder = "ORDER BY F.IN_EXCEL_TABLE";
		elseif ($by == "s_field_type")			$strSqlOrder = "ORDER BY F.FIELD_TYPE";
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
		$strSql = "
			SELECT
				F.*,
				F.SID as VARNAME,
				".$DB->DateToCharFunction("F.TIMESTAMP_X")."	TIMESTAMP_X
			FROM
				b_form_field F
			WHERE
			$strSqlSearch
			$str
			and FORM_ID='$WEB_FORM_ID'
			$strSqlOrder
			";
		$res = $DB->Query($strSql);

		return $res;
	}

	public static function GetByID($ID)
	{
		global $DB;
		$ID = intval($ID);
		$strSql = "
			SELECT
				F.*,
				F.SID as VARNAME,
				".$DB->DateToCharFunction("F.TIMESTAMP_X")."	TIMESTAMP_X
			FROM b_form_field F
			WHERE F.ID = $ID
			";
		$res = $DB->Query($strSql);
		return $res;
	}

	public static function GetBySID($SID, $FORM_ID = false)
	{
		$FORM_ID = intval($FORM_ID);

		global $DB;
		$strSql = "
			SELECT
				F.*,
				F.SID as VARNAME,
				".$DB->DateToCharFunction("F.TIMESTAMP_X")."	TIMESTAMP_X
			FROM b_form_field F
			WHERE F.SID = '".$DB->ForSql($SID,50)."'
			";
		if ($FORM_ID > 0)
			$strSql .= " AND F.FORM_ID='".$DB->ForSql($FORM_ID)."'";

		$res = $DB->Query($strSql);

		return $res;
	}

	public static function GetNextSort($WEB_FORM_ID)
	{
		global $DB;
		$WEB_FORM_ID = intval($WEB_FORM_ID);
		$strSql = "SELECT max(C_SORT) as MAX_SORT FROM b_form_field WHERE FORM_ID='$WEB_FORM_ID'";
		$z = $DB->Query($strSql);
		$zr = $z->Fetch();
		return (intval($zr["MAX_SORT"])+100);
	}

	public static function Copy($ID, $CHECK_RIGHTS="Y", $NEW_FORM_ID=false)
	{
		global $DB, $strError;
		$ID = intval($ID);
		$NEW_FORM_ID = intval($NEW_FORM_ID);
		$rsField = CFormField::GetByID($ID);
		if ($arField = $rsField->Fetch())
		{
			$RIGHT_OK = "N";
			if ($CHECK_RIGHTS!="Y" || CForm::IsAdmin()) $RIGHT_OK="Y";
			else
			{
				$F_RIGHT = CForm::GetPermission($arField["FORM_ID"]);
				if ($F_RIGHT>=25)
				{
					if ($NEW_FORM_ID>0)
					{
						$NEW_F_RIGHT = CForm::GetPermission($NEW_FORM_ID);
						if ($NEW_F_RIGHT>=30) $RIGHT_OK = "Y";
					}
					elseif ($F_RIGHT>=30)
					{
						$RIGHT_OK = "Y";
					}
				}
			}

			if ($RIGHT_OK=="Y")
			{
				if (!$NEW_FORM_ID)
				{
					while(true)
					{
						$SID = $arField["SID"];
						if (mb_strlen($SID) > 44) $SID = mb_substr($SID, 0, 44);
						$SID .= "_".RandString(5);


						$strSql = "SELECT 'x' FROM b_form WHERE SID='".$DB->ForSql($SID,50)."'";
						$z = $DB->Query($strSql);
						if (!($zr = $z->Fetch()))
						{
							$strSql = "SELECT 'x' FROM b_form_field WHERE SID='".$DB->ForSql($SID,50)."' AND FORM_ID='".$arField["FORM_ID"]."'";
							$t = $DB->Query($strSql);
							if (!($tr = $t->Fetch())) break;
						}
					}
				}
				else
				{
					$SID = $arField["SID"];
				}

				$arFields = array(
					"FORM_ID"				=> ($NEW_FORM_ID>0) ? $NEW_FORM_ID : $arField["FORM_ID"],
					"ACTIVE"				=> $arField["ACTIVE"],
					"TITLE"					=> $arField["TITLE"],
					"TITLE_TYPE"			=> $arField["TITLE_TYPE"],
					"SID"					=> $SID,
					"C_SORT"				=> $arField["C_SORT"],
					"ADDITIONAL"			=> $arField["ADDITIONAL"],
					"REQUIRED"				=> $arField["REQUIRED"],
					"IN_FILTER"				=> $arField["IN_FILTER"],
					"IN_RESULTS_TABLE"		=> $arField["IN_RESULTS_TABLE"],
					"IN_EXCEL_TABLE"		=> $arField["IN_EXCEL_TABLE"],
					"FIELD_TYPE"			=> $arField["FIELD_TYPE"],
					"COMMENTS"				=> $arField["COMMENTS"],
					"FILTER_TITLE"			=> $arField["FILTER_TITLE"],
					"RESULTS_TABLE_TITLE"	=> $arField["RESULTS_TABLE_TITLE"],
					);

				if (intval($arField["IMAGE_ID"])>0)
				{
					$arIMAGE = CFile::MakeFileArray(CFile::CopyFile($arField["IMAGE_ID"]));
					$arIMAGE["MODULE_ID"] = "form";
					$arFields["arIMAGE"] = $arIMAGE;
				}

				$z = CFormField::GetFilterList($arField["FORM_ID"], Array("FIELD_ID" => $ID, "FIELD_ID_EXACT_MATCH" => "Y"));
				while ($zr = $z->Fetch())
				{
					if ($arField["ADDITIONAL"]!="Y") $arFields["arFILTER_".$zr["PARAMETER_NAME"]][] = $zr["FILTER_TYPE"];
					elseif ($zr["PARAMETER_NAME"]=="USER") $arFields["arFILTER_FIELD"][] = $zr["FILTER_TYPE"];
				}
				$NEW_ID = CFormField::Set($arFields);
				if (intval($NEW_ID)>0)
				{
					if ($arField["ADDITIONAL"]!="Y")
					{
						$rsAnswer = CFormAnswer::GetList($ID, 'ID', 'ASC');
						while ($arAnswer = $rsAnswer->Fetch())
							CFormAnswer::Copy($arAnswer["ID"], $NEW_ID);

						$dbValidators = CFormValidator::GetList($ID);
						while ($arVal = $dbValidators->Fetch())
						{
							CFormValidator::Set($arField['FORM_ID'], $NEW_ID, $arVal['NAME'], $arVal['PARAMS'], $arVal['C_SORT']);
						}
					}
				}
				return $NEW_ID;
			}
			else $strError .= GetMessage("FORM_ERROR_ACCESS_DENIED")."<br>";
		}
		else $strError .= GetMessage("FORM_ERROR_FIELD_NOT_FOUND")."<br>";
		return false;
	}

	public static function Delete($ID, $CHECK_RIGHTS="Y")
	{
		global $DB, $strError;
		$ID = intval($ID);

		$rsField = CFormField::GetByID($ID);
		if ($arField = $rsField->Fetch())
		{
			$WEB_FORM_ID = intval($arField["FORM_ID"]);

			$F_RIGHT = ($CHECK_RIGHTS!="Y") ? 30 : CForm::GetPermission($WEB_FORM_ID);
			if ($F_RIGHT>=30)
			{
				CFormField::Reset($ID, $CHECK_RIGHTS);
				// clear field validators
				CFormValidator::Clear($ID);

				$strSql = "SELECT IMAGE_ID FROM b_form_field WHERE ID='$ID' and IMAGE_ID>0";
				$z = $DB->Query($strSql);
				while ($zr = $z->Fetch())
					CFile::Delete($zr["IMAGE_ID"]);

				$DB->Query("DELETE FROM b_form_answer WHERE FIELD_ID='$ID'");

				$DB->Query("DELETE FROM b_form_field_filter WHERE FIELD_ID='$ID'");

				$DB->Query("DELETE FROM b_form_field WHERE ID='$ID'");

				return true;
			}
			else $strError .= GetMessage("FORM_ERROR_ACCESS_DENIED")."<br>";
		}
		else $strError .= GetMessage("FORM_ERROR_FIELD_NOT_FOUND")."<br>";
		return false;
	}

	public static function Reset($ID, $CHECK_RIGHTS="Y")
	{
		global $DB, $strError;
		$ID = intval($ID);

		$rsField = CFormField::GetByID($ID);
		if ($arField = $rsField->Fetch())
		{
			$WEB_FORM_ID = intval($arField["FORM_ID"]);

			$F_RIGHT = ($CHECK_RIGHTS!="Y") ? 30 : CForm::GetPermission($WEB_FORM_ID);
			if ($F_RIGHT>=30)
			{
				$DB->Query("DELETE FROM b_form_result_answer WHERE FIELD_ID='".$ID."'");

				return true;
			}
			else $strError .= GetMessage("FORM_ERROR_ACCESS_DENIED")."<br>";
		}
		else $strError .= GetMessage("FORM_ERROR_FIELD_NOT_FOUND")."<br>";
		return false;
	}

	public static function GetFilterTypeList(&$arrUSER, &$arrANSWER_TEXT, &$arrANSWER_VALUE, &$arrFIELD)
	{
		$arrUSER = array(
			"reference_id" => array(
				"text",
				"integer",
				"date",
				"exist",
				),
			"reference" => array(
				GetMessage("FORM_TEXT_FIELD"),
				GetMessage("FORM_NUMERIC_INTERVAL"),
				GetMessage("FORM_DATE_INTERVAL"),
				GetMessage("FORM_EXIST_FLAG"),
				)
			);
		$arrANSWER_TEXT = array(
			"reference_id" => array(
				"text",
				"integer",
				"dropdown",
				"exist",
				),
			"reference" => array(
				GetMessage("FORM_TEXT_FIELD"),
				GetMessage("FORM_NUMERIC_INTERVAL"),
				GetMessage("FORM_DROPDOWN_LIST"),
				GetMessage("FORM_EXIST_FLAG"),
				)
			);
		$arrANSWER_VALUE = array(
			"reference_id" => array(
				"text",
				"integer",
				"dropdown",
				"exist",
				),
			"reference" => array(
				GetMessage("FORM_TEXT_FIELD"),
				GetMessage("FORM_NUMERIC_INTERVAL"),
				GetMessage("FORM_DROPDOWN_LIST"),
				GetMessage("FORM_EXIST_FLAG"),
				)
			);
		$arrFIELD = array(
			"reference_id" => array(
				"text",
				"integer",
				"date",
				"exist",
				),
			"reference" => array(
				GetMessage("FORM_TEXT_FIELD"),
				GetMessage("FORM_NUMERIC_INTERVAL"),
				GetMessage("FORM_DATE_INTERVAL"),
				GetMessage("FORM_EXIST_FLAG"),
				)
			);
	}

	public static function GetTypeList()
	{
		$arr = array(
			"reference_id" => array(
				"text",
				"integer",
				"date"),
			"reference" => array(
				GetMessage("FORM_FIELD_TEXT"),
				GetMessage("FORM_FIELD_INTEGER"),
				GetMessage("FORM_FIELD_DATE")
				)
			);
		return $arr;
	}

	public static function GetFilterList($WEB_FORM_ID, $arFilter=Array())
	{
		global $DB;
		$WEB_FORM_ID = intval($WEB_FORM_ID);
		$arSqlSearch = Array();
		$strSqlSearch = "";
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
				$key = mb_strtoupper($key);
				switch($key)
				{
					case "FIELD_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("F.ID",$val,$match);
						break;
					case "FIELD_SID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("F.SID",$val,$match);
					break;
					case "ACTIVE":
						$arSqlSearch[] = ($val=="Y") ? "F.ACTIVE='Y'" : "F.ACTIVE='N'";
						break;
					case "FILTER_TYPE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("L.FILTER_TYPE", $val, $match);
						break;
					case "PARAMETER_NAME":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("L.PARAMETER_NAME", $val, $match);
						break;
				}
			}
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				F.*,
				F.SID as VARNAME,
				L.PARAMETER_NAME,
				L.FILTER_TYPE
			FROM
				b_form_field F,
				b_form_field_filter	L
			WHERE
			$strSqlSearch
			and F.FORM_ID = $WEB_FORM_ID
			and F.IN_FILTER = 'Y'
			and L.FIELD_ID = F.ID
			ORDER BY F.C_SORT, L.PARAMETER_NAME, L.FILTER_TYPE desc
			";
		$res = $DB->Query($strSql);
		return $res;
	}

	public static function CheckFields(&$arFields, $FIELD_ID, $CHECK_RIGHTS="Y")
	{
		global $DB, $strError;
		$str = "";
		$FIELD_ID = intval($FIELD_ID);
		$FORM_ID = intval($arFields["FORM_ID"]);
		if ($FORM_ID<=0) $str .= GetMessage("FORM_ERROR_FORM_ID_NOT_DEFINED")."<br>";
		else
		{
			$RIGHT_OK = "N";
			if ($CHECK_RIGHTS!="Y" || CForm::IsAdmin()) $RIGHT_OK = "Y";
			else
			{
				$F_RIGHT = CForm::GetPermission($FORM_ID);
				if ($F_RIGHT>=30) $RIGHT_OK = "Y";
			}

			if ($RIGHT_OK=="Y")
			{
				if (trim($arFields["SID"]) <> '') $arFields["VARNAME"] = $arFields["SID"];
				elseif ($arFields["VARNAME"] <> '') $arFields["SID"] = $arFields["VARNAME"];

				if ($FIELD_ID<=0 && !is_set($arFields, 'ADDITIONAL'))
					$arFields['ADDITIONAL'] = 'N';

				if ($FIELD_ID<=0 || ($FIELD_ID>0 && is_set($arFields, "SID")))
				{
					if (trim($arFields["SID"]) == '') $str .= GetMessage("FORM_ERROR_FORGOT_SID")."<br>";
					if (preg_match("/[^A-Za-z_01-9]/",$arFields["SID"])) $str .= GetMessage("FORM_ERROR_INCORRECT_SID")."<br>";
					else
					{
						$strSql = "SELECT ID, ADDITIONAL FROM b_form_field WHERE SID='".$DB->ForSql(trim($arFields["SID"]),50)."' and ID<>'".$FIELD_ID."' AND FORM_ID='".$DB->ForSql($arFields["FORM_ID"])."'";
						$z = $DB->Query($strSql);
						if ($zr = $z->Fetch())
						{
							$s = ($zr["ADDITIONAL"]=="Y") ?
								str_replace("#TYPE#", GetMessage("FORM_TYPE_FIELD"), GetMessage("FORM_ERROR_WRONG_SID")) :
								str_replace("#TYPE#", GetMessage("FORM_TYPE_QUESTION"), GetMessage("FORM_ERROR_WRONG_SID"));
							$s = str_replace("#ID#",$zr["ID"],$s);
							$str .= $s."<br>";
						}
						else
						{
							$strSql = "SELECT ID FROM b_form WHERE SID='".$DB->ForSql(trim($arFields["SID"]),50)."'";
							$z = $DB->Query($strSql);
							if ($zr = $z->Fetch())
							{
								$s = str_replace("#TYPE#", GetMessage("FORM_TYPE_FORM"), GetMessage("FORM_ERROR_WRONG_SID"));
								$s = str_replace("#ID#",$zr["ID"],$s);
								$str .= $s."<br>";
							}
						}
					}
				}

				if (isset($arFields["arIMAGE"]))
				{
					$str .= CFile::CheckImageFile($arFields["arIMAGE"]);
				}
			}
			else $str .= GetMessage("FORM_ERROR_ACCESS_DENIED");
		}

		$strError .= $str;
		if ($str <> '') return false; else return true;
	}

	public static function Set($arFields, $FIELD_ID=false, $CHECK_RIGHTS="Y", $UPDATE_FILTER="Y")
	{
		global $DB;

		if (CFormField::CheckFields($arFields, $FIELD_ID, $CHECK_RIGHTS))
		{
			$arFields_i = array();

			if (trim($arFields["SID"]) <> '') $arFields["VARNAME"] = $arFields["SID"];
			elseif ($arFields["VARNAME"] <> '') $arFields["SID"] = $arFields["VARNAME"];

			$arFields_i["TIMESTAMP_X"] = $DB->GetNowFunction();

			if (is_set($arFields, "ACTIVE"))
				$arFields_i["ACTIVE"] = ($arFields["ACTIVE"]=="Y") ? "'Y'" : "'N'";

			if (is_set($arFields, "TITLE"))
				$arFields_i["TITLE"] = "'".$DB->ForSql($arFields["TITLE"], 2000)."'";

			if (is_set($arFields, "TITLE_TYPE"))
				$arFields_i["TITLE_TYPE"] = ($arFields["TITLE_TYPE"]=="html") ? "'html'" : "'text'";

			if (is_set($arFields, "SID"))
				$arFields_i["SID"] = "'".$DB->ForSql($arFields["SID"],50)."'";

			if (is_set($arFields, "C_SORT"))
				$arFields_i["C_SORT"] = "'".intval($arFields["C_SORT"])."'";

			if (is_set($arFields, "ADDITIONAL"))
				$arFields_i["ADDITIONAL"] = ($arFields["ADDITIONAL"]=="Y") ? "'Y'" : "'N'";

			if (is_set($arFields, "REQUIRED"))
				$arFields_i["REQUIRED"] = ($arFields["REQUIRED"]=="Y") ? "'Y'" : "'N'";

			if (is_set($arFields, "IN_RESULTS_TABLE"))
				$arFields_i["IN_RESULTS_TABLE"] = ($arFields["IN_RESULTS_TABLE"]=="Y") ? "'Y'" : "'N'";

			if (is_set($arFields, "IN_EXCEL_TABLE"))
				$arFields_i["IN_EXCEL_TABLE"] = ($arFields["IN_EXCEL_TABLE"]=="Y") ? "'Y'" : "'N'";

			if (is_set($arFields, "FIELD_TYPE"))
				$arFields_i["FIELD_TYPE"] = "'".$DB->ForSql($arFields["FIELD_TYPE"],50)."'";

			if (is_set($arFields, "COMMENTS"))
				$arFields_i["COMMENTS"] = "'".$DB->ForSql($arFields["COMMENTS"],2000)."'";

			if (is_set($arFields, "FILTER_TITLE"))
				$arFields_i["FILTER_TITLE"] = "'".$DB->ForSql($arFields["FILTER_TITLE"],2000)."'";

			if (is_set($arFields, "RESULTS_TABLE_TITLE"))
				$arFields_i["RESULTS_TABLE_TITLE"] = "'".$DB->ForSql($arFields["RESULTS_TABLE_TITLE"],2000)."'";

			if (!empty($arFields["arIMAGE"]["name"]) || !empty($arFields["arIMAGE"]["del"]))
			{
				if (!array_key_exists("MODULE_ID", $arFields["arIMAGE"]) || $arFields["arIMAGE"]["MODULE_ID"] == '')
					$arFields["arIMAGE"]["MODULE_ID"] = "form";

				$fid = CFile::SaveFile($arFields["arIMAGE"], "form");
				if (intval($fid)>0)	$arFields_i["IMAGE_ID"] = intval($fid);
				else $arFields_i["IMAGE_ID"] = "null";
			}

			$FIELD_ID = intval($FIELD_ID);

			if ($FIELD_ID>0)
			{
				$DB->Update("b_form_field", $arFields_i, "WHERE ID='".$FIELD_ID."'");
			}
			else
			{
				$arFields_i["FORM_ID"] = "'".intval($arFields["FORM_ID"])."'";
				$FIELD_ID = $DB->Insert("b_form_field", $arFields_i);
			}

			if ($FIELD_ID>0)
			{
				if ((!isset($arFields["ADDITIONAL"]) || $arFields["ADDITIONAL"] != "Y") && is_set($arFields, "arANSWER"))
				{
					$arANSWER = $arFields["arANSWER"];
					if (is_array($arANSWER) && count($arANSWER)>0)
					{
						$arrAnswers = array();
						$rs = CFormAnswer::GetList($FIELD_ID, 'ID', 'ASC');
						while($ar = $rs->Fetch())
							$arrAnswers[] = $ar["ID"];

						foreach($arANSWER as $arA)
						{
							$answer_id = in_array($arA["ID"], $arrAnswers) ? intval($arA["ID"]) : 0;
							if ($arA["DELETE"]=="Y" && $answer_id>0) CFormAnswer::Delete($answer_id, $FIELD_ID);
							else
							{
								if ($answer_id>0 || ($answer_id<=0 && $arA["MESSAGE"] <> ''))
								{
									$arFields_a = array(
										"FIELD_ID"		=> $FIELD_ID,
										"MESSAGE"		=> $arA["MESSAGE"],
										"VALUE"			=> $arA["VALUE"],
										"C_SORT"		=> $arA["C_SORT"],
										"ACTIVE"		=> $arA["ACTIVE"],
										"FIELD_TYPE"	=> $arA["FIELD_TYPE"],
										"FIELD_WIDTH"	=> $arA["FIELD_WIDTH"],
										"FIELD_HEIGHT"	=> $arA["FIELD_HEIGHT"],
										"FIELD_PARAM"	=> $arA["FIELD_PARAM"],
										);
									CFormAnswer::Set($arFields_a, $answer_id, $FIELD_ID);
								}
							}
						}
					}
				}

				CForm::SetMailTemplate(intval($arFields["FORM_ID"]),"N");

				if ($UPDATE_FILTER == 'Y')
				{
					$in_filter="N";
					$DB->Query("UPDATE b_form_field SET IN_FILTER='N' WHERE ID='".$FIELD_ID."'");
					$arrFilterType = array(
						"arFILTER_USER"			=> "USER",
						"arFILTER_ANSWER_TEXT"	=> "ANSWER_TEXT",
						"arFILTER_ANSWER_VALUE"	=> "ANSWER_VALUE",
						"arFILTER_FIELD"		=> "USER",
					);

					foreach ($arrFilterType as $key => $value)
					{
						if (is_set($arFields, $key))
						{
							$strSql = "DELETE FROM b_form_field_filter WHERE FIELD_ID='".$FIELD_ID."' and PARAMETER_NAME='".$value."'";
							$DB->Query($strSql);
							if (is_array($arFields[$key]))
							{
								reset($arFields[$key]);
								foreach($arFields[$key] as $type)
								{
									$arFields_i = array(
										"FIELD_ID"			=> "'".intval($FIELD_ID)."'",
										"FILTER_TYPE"		=> "'".$DB->ForSql($type,50)."'",
										"PARAMETER_NAME"	=> "'".$value."'",
									);
									$DB->Insert("b_form_field_filter",$arFields_i);
									$in_filter="Y";
								}
							}
						}
					}

					if ($in_filter=="Y")
						$DB->Query("UPDATE b_form_field SET IN_FILTER='Y' WHERE ID='".$FIELD_ID."'");
				}
			}
			return $FIELD_ID;
		}
		return false;
	}
}
