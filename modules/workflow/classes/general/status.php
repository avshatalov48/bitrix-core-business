<?php
IncludeModuleLangFile(__FILE__);

class CWorkflowStatus
{
	//Despite this function is not documented it should be version compatible
	public static function GetList($by = 's_c_sort', $order = 'asc', $arFilter = [], $is_filtered = null, $arSelect = [])
	{
		global $DB;

		if(!is_array($arSelect))
			$arSelect = array();
		if(count($arSelect) <= 0)
			$arSelect = array("ID", "C_SORT", "ACTIVE", "TITLE", "DESCRIPTION", "IS_FINAL", "TIMESTAMP_X", "DOCUMENTS", "NOTIFY");

		if($by == "s_id")
		{
			$strSqlOrder = "ORDER BY S.ID";
			$arSelect[] = "ID";
		}
		elseif($by == "s_timestamp")
		{
			$strSqlOrder = "ORDER BY S.TIMESTAMP_X";
			$arSelect[] = "TIMESTAMP_X";
		}
		elseif($by == "s_active")
		{
			$strSqlOrder = "ORDER BY S.ACTIVE";
			$arSelect[] = "ACTIVE";
		}
		elseif($by == "s_c_sort")
		{
			$strSqlOrder = "ORDER BY S.C_SORT";
			$arSelect[] = "C_SORT";
		}
		elseif($by == "s_title")
		{
			$strSqlOrder = "ORDER BY S.TITLE ";
			$arSelect[] = "TITLE";
		}
		elseif($by == "s_description")
		{
			$strSqlOrder = "ORDER BY S.DESCRIPTION";
			$arSelect[] = "DESCRIPTION";
		}
		elseif($by == "s_documents")
		{
			$strSqlOrder = "ORDER BY DOCUMENTS";
			$arSelect[] = "DOCUMENTS";
		}
		else
		{
			$strSqlOrder = "ORDER BY S.C_SORT";
			$arSelect[] = "C_SORT";
		}

		if($order != "desc")
			$order = "asc";

		$strSqlOrder .= " $order ";

		$arSelectFields = array(
			"ID" => "S.ID",
			"C_SORT" => "S.C_SORT",
			"ACTIVE" => "S.ACTIVE",
			"NOTIFY" => "S.NOTIFY",
			"TITLE" => "S.TITLE",
			"DESCRIPTION" => "S.DESCRIPTION",
			"IS_FINAL" => "S.IS_FINAL",
			"TIMESTAMP_X" => "S.TIMESTAMP_X TIMESTAMP_X_TEMP, ".$DB->DateToCharFunction("S.TIMESTAMP_X"),
			"DOCUMENTS" => "count(DISTINCT D.ID)",
			"REFERENCE_ID" => "S.ID",
			"REFERENCE" => ($DB->type=="MSSQL"?
					$DB->Concat("'['", "convert(varchar(8000), S.ID)" , "'] '", "S.TITLE"):
					$DB->Concat("'['", "S.ID" , "'] '", "S.TITLE")
					),
		);
		$arSqlSelect = array();
		foreach($arSelect as $field)
			if(array_key_exists($field, $arSelectFields))
				$arSqlSelect[$field] = $arSelectFields[$field]." ".$field;

		$bGroup = false;
		$arGroupFields = array(
			"ID" => "S.ID",
			"C_SORT" => "S.C_SORT",
			"ACTIVE" => "S.ACTIVE",
			"NOTIFY" => "S.NOTIFY",
			"TITLE" => "S.TITLE",
			"DESCRIPTION" => "S.DESCRIPTION",
			"IS_FINAL" => "S.IS_FINAL",
			"TIMESTAMP_X" => "S.TIMESTAMP_X",
			"REFERENCE_ID" => "S.ID",
			"REFERENCE" => ($DB->type=="MSSQL"?
					$DB->Concat("'['", "convert(varchar(8000), S.ID)" , "'] '", "S.TITLE"):
					$DB->Concat("'['", "S.ID" , "'] '", "S.TITLE")
					),
		);
		$arSqlGroup = array();
		foreach($arSelect as $field)
			if(array_key_exists($field, $arGroupFields))
				$arSqlGroup[$field] = $arGroupFields[$field];
			elseif(array_key_exists($field, $arSelectFields))
			{
				$arSqlGroup["ID"] = "S.ID";
				$bGroup = true;
			}

		$arSqlSearch = $arSqlSearch_h = $arSqlSearch_g = array();

		if(is_array($arFilter))
		{
			foreach($arFilter as $key => $val)
			{
				if(is_array($val))
				{
					if(count($val) <= 0) continue;
				}
				else
				{
					if((string)$val == '' || "$val"=="NOT_REF") continue;
				}

				$match_value_set = array_key_exists($key."_EXACT_MATCH", $arFilter);
				$key = strtoupper($key);
				$predicate = "";
				switch($key)
				{
					case "ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$predicate = GetFilterQuery("S.ID", $val, $match);
						break;
					case "ACTIVE":
						$predicate = ($val=="Y") ? "S.ACTIVE='Y'" : "S.ACTIVE='N'";
						break;
					case "!=ACTIVE":
						if($val === "Y" || $val === "N")
							$arSqlSearch[] = "S.ACTIVE <> '".$val."'";
						break;
					case "TITLE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$predicate = GetFilterQuery("S.TITLE", $val, $match);
						break;
					case "DESCRIPTION":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$predicate = GetFilterQuery("S.DESCRIPTION", $val, $match);
						break;
					case "DOCUMENTS_1":
						$arSqlSearch_h[] = "count(D.ID) >= ".intval($val);
						$bGroup = true;
						break;
					case "DOCUMENTS_2":
						$arSqlSearch_h[] = "count(D.ID) <= ".intval($val);
						$bGroup = true;
						break;
					case "GROUP_ID":
						if(!is_array($val))
							$val = array($val);
						$groups = array();
						foreach($val as $i => $v)
						{
							$v = intval($v);
							if($v > 0)
								$groups[$v] = $v;
						}
						if(count($groups) > 0)
						{
							$arSqlSearch_g[] = "G.GROUP_ID in (".implode(", ",$groups).")";
							$bGroup = true;
						}
						break;
					case "PERMISSION_TYPE_1":
						$val = intval($val);
						if($val > 0)
						{
							$arSqlSearch_g[] = "G.PERMISSION_TYPE >= ".$val;
							$bGroup = true;
						}
						break;
					case "PERMISSION_TYPE_2":
						$val = intval($val);
						if($val > 0)
						{
							$arSqlSearch_g[] = "G.PERMISSION_TYPE <= ".$val;
							$bGroup = true;
						}
						break;
				}
				if($predicate <> '' && $predicate!="0")
					$arSqlSearch[] = $predicate;
			}
		}

		if(count($arSqlSearch) > 0)
			$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		else
			$strSqlSearch = "";

		if(count($arSqlSearch_h) > 0)
			$strSqlSearch_h = "(".implode(") and (", $arSqlSearch_h).") ";
		else
			$strSqlSearch_h = "";

		if(count($arSqlSearch_g) > 0)
		{
			if($strSqlSearch <> '')
			{
				$strSqlSearch .= " AND ";
			}
			$strSqlSearch .= "(".implode(") and (", $arSqlSearch_g).") ";
		}

		$strSql = "
			SELECT
				".implode(", ", $arSqlSelect)."
			FROM
				b_workflow_status S
			".($strSqlSearch_h <> '' || array_key_exists("DOCUMENTS", $arSqlSelect)? "LEFT JOIN b_workflow_document D ON (D.STATUS_ID = S.ID)": "")."
			".(count($arSqlSearch_g) > 0? "LEFT JOIN b_workflow_status2group G ON (G.STATUS_ID = S.ID)": "")."
			".($strSqlSearch <> ''? "WHERE ".$strSqlSearch: "")."
			".($bGroup? "GROUP BY ".implode(", ", $arSqlGroup): "")."
			".($strSqlSearch_h <> ''? "HAVING ".$strSqlSearch_h: "")."
			$strSqlOrder
			";

		$res = $DB->Query($strSql);

		return $res;
	}

	public static function GetByID($ID)
	{
		return CWorkflowStatus::GetList('', '', array("ID" => $ID, "ID_EXACT_MATCH" => "Y"));
	}

	public static function GetDropDownList($SHOW_ALL="N", $strOrder = "desc", $arFilter = array())
	{
		global $USER;

		if(strtolower($strOrder) != "asc")
			$strOrder = "desc";
		else
			$strOrder = "asc";

		$arFilter["!=ACTIVE"] = "N";
		if(!(CWorkflow::IsAdmin() || $SHOW_ALL=="Y"))
		{
			$arGroups = $USER->GetUserGroupArray();
			if(!is_array($arGroups))
				$arGroups = array(2);
			$arFilter["GROUP_ID"] = $arGroups;
			$arFilter["PERMISSION_TYPE_1"] = 1;
		}

		return CWorkflowStatus::GetList("s_c_sort", $strOrder, $arFilter, null, array("REFERENCE_ID", "REFERENCE", "IS_FINAL", "C_SORT"));
	}

	public static function GetNextSort()
	{
		global $DB;

		$strSql = "SELECT max(C_SORT) MAX_SORT FROM b_workflow_status";
		$z = $DB->Query($strSql);
		$zr = $z->Fetch();
		return intval($zr["MAX_SORT"])+100;
	}

	//check fields before writing
	function CheckFields($ID, $arFields)
	{
		$aMsg = array();

		$ID = intval($ID);

		if(($ID <= 0) && (trim($arFields["TITLE"]) == ''))
			$aMsg[] = array("id"=>"TITLE", "text"=> GetMessage("FLOW_FORGOT_TITLE"));

		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}
		return true;
	}

	//add
	function Add($arFields)
	{
		global $DB;

		if(!$this->CheckFields(0, $arFields))
			return false;

		$ID = $DB->Add("b_workflow_status", $arFields);

		if(($ID == 1) && ($arFields["ACTIVE"] != "Y"))
		{
			$this->Update($ID, array("ACTIVE" => "Y"));
		}

		return $ID;
	}

	//update
	function Update($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);

		if(($ID == 1) && array_key_exists("ACTIVE", $arFields))
			$arFields["ACTIVE"] = "Y";

		if(!$this->CheckFields($ID, $arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_workflow_status", $arFields);
		if($strUpdate != "")
		{
			$strSql = "UPDATE b_workflow_status SET ".$strUpdate." WHERE ID = ".$ID;
			$DB->Query($strSql);
		}
		return true;
	}

	function SetPermissions($STATUS_ID, $arGroups, $PERMISSION_TYPE = 1)
	{
		global $DB;

		$STATUS_ID = intval($STATUS_ID);
		$PERMISSION_TYPE = intval($PERMISSION_TYPE);

		$DB->Query("DELETE FROM b_workflow_status2group WHERE STATUS_ID = ".$STATUS_ID." AND PERMISSION_TYPE = ".$PERMISSION_TYPE);
		if(is_array($arGroups) && ($PERMISSION_TYPE == 1 || $PERMISSION_TYPE == 2))
		{
			foreach ($arGroups as $GROUP_ID)
			{
				$GROUP_ID = intval($GROUP_ID);
				$arFields = array(
					"STATUS_ID" => $STATUS_ID,
					"GROUP_ID" => $GROUP_ID,
					"PERMISSION_TYPE" => $PERMISSION_TYPE,
				);
				$DB->Insert("b_workflow_status2group", $arFields);
			}
		}
	}
}
